/**
 * Audio Preload & Offline Cache Manager
 * Menyimpan seluruh audio di IndexedDB client dan mengonversinya menjadi URL blob lokal
 * agar pemutaran suara instan, tanpa jeda download, dan tidak pernah tertinggal.
 */
(function() {
    window.SoundCache = {
        dbName: 'MarshallingSoundCache',
        dbVersion: 1,
        storeName: 'audios',
        db: null,
        blobUrls: {},
        isReady: false,
        readyCallbacks: [],

        items: [
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
            '10', '11', '100', '1000',
            'belas', 'puluh', 'ratus', 'ribu',
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
            'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't',
            'u', 'v', 'w', 'x', 'y', 'z', 'boks'
        ],
        cacheVersion: '20260917_v2',
        themes: ['a', 'b'],

        init: function(baseUrl) {
            var self = this;
            self.baseUrl = baseUrl.replace(/\/$/, '') + '/assets/sounds/';

            var request = indexedDB.open(self.dbName, self.dbVersion);
            request.onupgradeneeded = function(e) {
                var db = e.target.result;
                if (!db.objectStoreNames.contains(self.storeName)) {
                    db.createObjectStore(self.storeName);
                }
            };

            request.onsuccess = function(e) {
                self.db = e.target.result;
                self.loadAllBlobs();
            };

            request.onerror = function(e) {
                console.warn('IndexedDB failed to open, falling back to direct URLs:', e);
                self.setReady();
            };
        },

        onReady: function(cb) {
            if (this.isReady) {
                cb();
            } else {
                this.readyCallbacks.push(cb);
            }
        },

        setReady: function() {
            this.isReady = true;
            while (this.readyCallbacks.length > 0) {
                var cb = this.readyCallbacks.shift();
                try { cb(); } catch (err) { console.error(err); }
            }
        },

        getCacheKey: function(theme, name) {
            return theme + '_' + name;
        },

        getUrl: function(theme, name) {
            var key = this.getCacheKey(theme, name);
            if (this.blobUrls[key]) {
                return this.blobUrls[key];
            }
            // Fallback direct network URL jika belum ada di blob cache
            return this.baseUrl + theme + '/' + name + '.mp3';
        },

        loadAllBlobs: function() {
            var self = this;
            var tx = self.db.transaction(self.storeName, 'readonly');
            var store = tx.objectStore(self.storeName);
            var req = store.openCursor();

            req.onsuccess = function(e) {
                var cursor = e.target.result;
                if (cursor) {
                    var key = cursor.key;
                    var blob = cursor.value;
                    if (blob instanceof Blob) {
                        self.blobUrls[key] = URL.createObjectURL(blob);
                    }
                    cursor.continue();
                } else {
                    self.setReady();
                    // Background sync / unduh file yang belum ada di IndexedDB
                    self.syncMissingAudios();
                }
            };

            req.onerror = function() {
                self.setReady();
                self.syncMissingAudios();
            };
        },

        syncMissingAudios: function() {
            var self = this;
            self.themes.forEach(function(theme) {
                self.items.forEach(function(item) {
                    var key = self.getCacheKey(theme, item);
                    if (!self.blobUrls[key]) {
                        self.fetchAndStore(theme, item);
                    }
                });
            });
        },

        fetchAndStore: function(theme, item) {
            var self = this;
            var url = self.baseUrl + theme + '/' + item + '.mp3?v=' + encodeURIComponent(self.cacheVersion);
            var key = self.getCacheKey(theme, item);

            fetch(url)
                .then(function(res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.blob();
                })
                .then(function(blob) {
                    self.blobUrls[key] = URL.createObjectURL(blob);
                    if (self.db) {
                        try {
                            var tx = self.db.transaction(self.storeName, 'readwrite');
                            tx.objectStore(self.storeName).put(blob, key);
                        } catch (err) {
                            console.warn('Could not save sound to IndexedDB:', err);
                        }
                    }
                })
                .catch(function(err) {
                    // Abaikan error download offline sementara
                });
        }
    };
})();
