let countDetections = [];
let countManualAdds = [];
let countOriginalImage = null;
let countOriginalDataUrl = null;
let lastTemplateRect = null;
let finalCount = 0;
let countStream = null;
let drawState = null;

async function startCountCamera() {
    try {
        if (countStream) countStream.getTracks().forEach(t => t.stop());
        countStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } } });
        document.getElementById('countVideo').srcObject = countStream;
        $('#countCapturePrompt').hide();
        $('#countCameraContainer').show();
    } catch(e) { alert('Camera error: ' + e.message); }
}

function stopCountCamera() {
    if (countStream) { countStream.getTracks().forEach(t => t.stop()); countStream = null; }
    $('#countCameraContainer').hide();
    $('#countCapturePrompt').show();
}

function capturePhoto() {
    var video = document.getElementById('countVideo');
    var canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    countOriginalDataUrl = canvas.toDataURL('image/jpeg', 0.92);
    stopCountCamera();
    loadCountImage(countOriginalDataUrl);
}

function loadCountImage(dataUrl) {
    countOriginalDataUrl = dataUrl;
    $('#image_data').val(dataUrl);
    var img = new Image();
    img.onload = function() {
        var w = img.width, h = img.height;
        var MAX = 640;
        if (w > MAX || h > MAX) { var r = Math.min(MAX/w, MAX/h); w = Math.floor(w*r); h = Math.floor(h*r); }
        var canvas = document.getElementById('countCanvas');
        canvas.width = w; canvas.height = h;
        canvas.getContext('2d').drawImage(img, 0, 0, w, h);

        if (countOriginalImage && countOriginalImage.delete) countOriginalImage.delete();
        if (typeof cv !== 'undefined' && window.cvReady) {
            countOriginalImage = cv.imread(canvas);
        }

        countDetections = []; countManualAdds = []; lastTemplateRect = null;
        $('#countBadge').hide(); $('#countSensitivityArea').hide();
        $('#countCapturePrompt').hide();
        $('#countCanvasArea').show();
    };
    img.src = dataUrl;
}

function redrawCanvas() {
    if (!countOriginalImage) return;
    var canvas = document.getElementById('countCanvas');
    if (typeof cv !== 'undefined' && window.cvReady) {
        cv.imshow(canvas, countOriginalImage);
    }
    var ctx = canvas.getContext('2d');
    ctx.strokeStyle = '#00FF00'; ctx.lineWidth = 2;
    countDetections.forEach(function(d, i) {
        ctx.strokeRect(d.x, d.y, d.w, d.h);
        ctx.fillStyle = 'rgba(0,255,0,0.15)'; ctx.fillRect(d.x, d.y, d.w, d.h);
        ctx.fillStyle = '#00FF00'; ctx.font = 'bold 12px Arial'; ctx.fillText(i+1, d.x+2, d.y+12);
    });
    ctx.fillStyle = 'rgba(255,165,0,0.7)'; ctx.strokeStyle = '#FFA500';
    countManualAdds.forEach(function(p) {
        ctx.beginPath(); ctx.arc(p.x, p.y, 12, 0, Math.PI*2); ctx.stroke(); ctx.fill();
        ctx.fillStyle = '#fff'; ctx.font = 'bold 10px Arial'; ctx.fillText('+', p.x-4, p.y+4);
        ctx.fillStyle = 'rgba(255,165,0,0.7)';
    });
    if (drawState) {
        var x = Math.min(drawState.startX, drawState.curX);
        var y = Math.min(drawState.startY, drawState.curY);
        var w = Math.abs(drawState.curX - drawState.startX);
        var h = Math.abs(drawState.curY - drawState.startY);
        ctx.strokeStyle = '#FF0000'; ctx.lineWidth = 2; ctx.setLineDash([6, 4]);
        ctx.strokeRect(x, y, w, h);
        ctx.setLineDash([]);
        ctx.fillStyle = 'rgba(255,0,0,0.08)';
        ctx.fillRect(x, y, w, h);
    }
    var total = countDetections.length + countManualAdds.length;
    $('#countBadge').text(total).show();
    finalCount = total;
    if (total > 0) {
        $('#Qty_Record').val(total);
        window.checkFormReady && window.checkFormReady();
    }
}

function runFallbackMatching(rx, ry, rw, rh) {
    if (!countOriginalImage || !window.cvReady) return;
    $('#countProcessing').show(); $('#countProcessingText').text('Counting...');
    setTimeout(function() {
        try {
            var src = countOriginalImage;
            var gray = new cv.Mat();
            cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);
            cv.GaussianBlur(gray, gray, new cv.Size(5, 5), 0);
            var edges = new cv.Mat();
            cv.Canny(gray, edges, 50, 150);
            var contours = new cv.MatVector();
            var hierarchy = new cv.Mat();
            cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);
            var refContour = null;
            var refIdx = -1;
            var minDist = 99999;
            var cx = rx + rw / 2;
            var cy = ry + rh / 2;
            for (var i = 0; i < contours.size(); i++) {
                try {
                    var c = contours.get(i);
                    var rect = cv.boundingRect(c);
                    var inside = rect.x >= rx && rect.y >= ry && (rect.x + rect.w) <= (rx + rw) && (rect.y + rect.h) <= (ry + rh);
                    var overlapX = Math.max(0, Math.min(rect.x + rect.w, rx + rw) - Math.max(rect.x, rx));
                    var overlapY = Math.max(0, Math.min(rect.y + rect.h, ry + rh) - Math.max(rect.y, ry));
                    var cx2 = rect.x + rect.w / 2;
                    var cy2 = rect.y + rect.h / 2;
                    var d = Math.sqrt((cx2 - cx) * (cx2 - cx) + (cy2 - cy) * (cy2 - cy));
                    if (inside) { refContour = c; refIdx = i; minDist = 0; break; }
                    if ((overlapX > 0 && overlapY > 0) && d < minDist) { minDist = d; refContour = c; refIdx = i; }
                } catch (e) { continue; }
            }
            if (refContour && contours.size() > 1) {
                var refArea = cv.contourArea(refContour);
                var thr = parseFloat($('#countThreshold').val()) / 100.0 || 0.3;
                if (thr < 0.01) thr = 0.3;
                var boxes = [];
                for (var i = 0; i < contours.size(); i++) {
                    if (i === refIdx) continue;
                    try {
                        var c = contours.get(i);
                        var area = cv.contourArea(c);
                        if (area < refArea * 0.1 || area > refArea * 5) continue;
                        var score = cv.matchShapes(refContour, c, 2, 0);
                        if (score <= thr) {
                            var r = cv.boundingRect(c);
                            boxes.push({ x: r.x, y: r.y, w: r.width, h: r.height, score: 1 - score });
                        }
                    } catch (e) { continue; }
                }
                var refRect = cv.boundingRect(refContour);
                boxes.push({ x: refRect.x, y: refRect.y, w: refRect.width, h: refRect.height, score: 1 });
                edges.delete(); contours.delete(); hierarchy.delete();
                if (boxes.length > 1) {
                    countDetections = nms(boxes, 0.4);
                    gray.delete();
                    redrawCanvas();
                    $('#countProcessing').hide();
                    $('#countSensitivityArea').show();
                    return;
                }
            } else {
                edges.delete(); contours.delete(); hierarchy.delete();
            }
            var tx = Math.max(0, Math.floor(rx));
            var ty = Math.max(0, Math.floor(ry));
            var tw = Math.min(rw, gray.cols - tx);
            var th = Math.min(rh, gray.rows - ty);
            if (tw < 10 || th < 10) { gray.delete(); $('#countProcessing').hide(); return; }
            var tmplRoi = gray.roi(new cv.Rect(tx, ty, tw, th));
            var tmpl = new cv.Mat();
            tmplRoi.copyTo(tmpl);
            tmplRoi.delete();
            var result = new cv.Mat();
            cv.matchTemplate(gray, tmpl, result, cv.TM_CCOEFF_NORMED);
            var thr2 = parseFloat($('#countThreshold').val()) / 100.0 || 0.5;
            if (thr2 < 0.01) thr2 = 0.5;
            var boxes2 = [];
            for (var row = 0; row < result.rows; row++) {
                for (var col = 0; col < result.cols; col++) {
                    var val = result.floatAt(row, col);
                    if (val >= thr2) {
                        boxes2.push({ x: col, y: row, w: tw, h: th, score: val });
                    }
                }
            }
            tmpl.delete(); result.delete(); gray.delete();
            countDetections = nms(boxes2, 0.3);
            redrawCanvas();
            $('#countProcessing').hide();
            $('#countSensitivityArea').show();
        } catch (e) { console.error(e); $('#countProcessing').hide(); alert('Count failed: ' + e.message); }
    }, 100);
}

function nms(boxes, overlap) {
    if (!boxes.length) return [];
    boxes.sort(function(a,b) { return b.score - a.score; });
    var result = [];
    while (boxes.length) {
        var best = boxes.shift();
        result.push(best);
        boxes = boxes.filter(function(b) {
            var x1 = Math.max(best.x, b.x);
            var y1 = Math.max(best.y, b.y);
            var x2 = Math.min(best.x + best.w, b.x + b.w);
            var y2 = Math.min(best.y + best.h, b.y + b.h);
            var inter = Math.max(0, x2 - x1) * Math.max(0, y2 - y1);
            var union = best.w * best.h + b.w * b.h - inter;
            return inter / union < overlap;
        });
    }
    return result;
}

$(function() {
    $('#startCountCamera').on('click', startCountCamera);
    $('#closeCountCamera').on('click', stopCountCamera);
    $('#captureCountPhoto').on('click', capturePhoto);
    $('#countFileUpload').on('click', function() { $('#countPhotoInput').click(); });
    $('#countPhotoInput').on('change', function() {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(e) { loadCountImage(e.target.result); };
        reader.readAsDataURL(file);
    });

    $('#countCanvas').on('mousedown', function(e) {
        if (!countOriginalImage) return;
        var canvas = this;
        var rect = canvas.getBoundingClientRect();
        var scaleX = canvas.width / rect.width;
        var scaleY = canvas.height / rect.height;
        var cx = (e.clientX - rect.left) * scaleX;
        var cy = (e.clientY - rect.top) * scaleY;

        for (var i = countDetections.length - 1; i >= 0; i--) {
            var d = countDetections[i];
            if (Math.abs(cx - (d.x + d.w / 2)) < d.w / 2 && Math.abs(cy - (d.y + d.h / 2)) < d.h / 2) {
                countDetections.splice(i, 1); redrawCanvas(); return;
            }
        }
        for (var i = countManualAdds.length - 1; i >= 0; i--) {
            var p = countManualAdds[i];
            if (Math.abs(cx - p.x) < 15 && Math.abs(cy - p.y) < 15) {
                countManualAdds.splice(i, 1); redrawCanvas(); return;
            }
        }
        drawState = { startX: cx, startY: cy, curX: cx, curY: cy };
    });

    $('#countCanvas').on('mousemove', function(e) {
        if (!drawState) return;
        var canvas = this;
        var rect = canvas.getBoundingClientRect();
        var scaleX = canvas.width / rect.width;
        var scaleY = canvas.height / rect.height;
        drawState.curX = (e.clientX - rect.left) * scaleX;
        drawState.curY = (e.clientY - rect.top) * scaleY;
        redrawCanvas();
    });

    $('#countCanvas').on('mouseup', function(e) {
        if (!drawState) return;
        var canvas = this;
        var rect = canvas.getBoundingClientRect();
        var scaleX = canvas.width / rect.width;
        var scaleY = canvas.height / rect.height;
        var endX = (e.clientX - rect.left) * scaleX;
        var endY = (e.clientY - rect.top) * scaleY;
        var w = Math.abs(endX - drawState.startX);
        var h = Math.abs(endY - drawState.startY);
        var rx = Math.min(drawState.startX, endX);
        var ry = Math.min(drawState.startY, endY);
        drawState = null;
        if (w < 5 && h < 5) {
            if (countDetections.length > 0 || countManualAdds.length > 0) {
                countManualAdds.push({ x: Math.round(endX), y: Math.round(endY) });
                redrawCanvas();
            } else { redrawCanvas(); }
            return;
        }
        if (w >= 15 && h >= 15) {
            lastTemplateRect = { x: rx, y: ry, w: w, h: h };
            countDetections = []; countManualAdds = [];
            runFallbackMatching(rx, ry, w, h);
        } else { redrawCanvas(); }
    });

    $('#countCanvas').on('mouseleave', function() {
        if (drawState) { drawState = null; redrawCanvas(); }
    });

    $('#countThreshold').on('input', function() {
        $('#countThresholdLabel').text($(this).val() + '%');
    });
    $('#countThreshold').on('change', function() {
        if (lastTemplateRect) {
            countManualAdds = [];
            runFallbackMatching(lastTemplateRect.x, lastTemplateRect.y, lastTemplateRect.w, lastTemplateRect.h);
        }
    });

    $('#retakePhoto').on('click', async function() {
        if (countOriginalImage && countOriginalImage.delete) countOriginalImage.delete();
        countOriginalImage = null; countDetections = []; countManualAdds = []; finalCount = 0;
        $('#Qty_Record').val('');
        $('#countCanvasArea').hide();
        $('#countCapturePrompt').show();
        stopCountCamera();
    });

    $('#clearCount').on('click', function() {
        countDetections = []; countManualAdds = []; finalCount = 0; lastTemplateRect = null;
        $('#Qty_Record').val('');
        if (countOriginalImage && typeof cv !== 'undefined' && window.cvReady) {
            redrawCanvas();
        }
        $('#countBadge').hide(); $('#countSensitivityArea').hide();
    });
});
