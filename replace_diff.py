import os
import re

files = [
    (r"c:\xampp\htdocs\iseki_marshalling\resources\views\perakitan\kanban\detail.blade.php",
     r"\{\{ \$rl->Difference \?\? '-' \}\}",
     r"{!! str_replace(';', '<br>', $rl->Difference ?? '-') !!}"),
    
    (r"c:\xampp\htdocs\iseki_marshalling\resources\views\admin\marshallings\index.blade.php",
     r"\{ data: 'Difference', name: 'Difference' \},",
     r"{ data: 'Difference', name: 'Difference', render: function(data) { return data ? data.replace(/;/g, '<br>') : '-'; } },"),
     
    (r"c:\xampp\htdocs\iseki_marshalling\resources\views\admin\records\empty-part.blade.php",
     r"\{ data: 'Difference', name: 'Difference' \},",
     r"{ data: 'Difference', name: 'Difference', render: function(data) { return data ? data.replace(/;/g, '<br>') : '-'; } },"),
     
    (r"c:\xampp\htdocs\iseki_marshalling\resources\views\admin\records\ng.blade.php",
     r"\{ data: 'Difference', name: 'Difference' \},",
     r"{ data: 'Difference', name: 'Difference', render: function(data) { return data ? data.replace(/;/g, '<br>') : '-'; } },"),
     
    (r"c:\xampp\htdocs\iseki_marshalling\resources\views\admin\records\ng.blade.php",
     r"html \+= '<strong>Difference:</strong> ' \+ \(resp\.Difference \|\| '-'\) \+ '<br>';",
     r"html += '<strong>Difference:</strong> ' + (resp.Difference ? resp.Difference.replace(/;/g, '<br>') : '-') + '<br>';"),
     
    (r"c:\xampp\htdocs\iseki_marshalling\resources\views\admin\dashboard.blade.php",
     r"\(rl\.Difference \|\| '-'\)",
     r"(rl.Difference ? rl.Difference.replace(/;/g, '<br>') : '-')"),
     
    (r"c:\xampp\htdocs\iseki_marshalling\resources\views\admin\records\index.blade.php",
     r"\(rl\.Difference \|\| '-'\)",
     r"(rl.Difference ? rl.Difference.replace(/;/g, '<br>') : '-')"),
     
    (r"c:\xampp\htdocs\iseki_marshalling\resources\views\member\record\record-part.blade.php",
     r"\{\{ \$rl->Difference \}\}",
     r"{!! str_replace(';', '<br>', $rl->Difference) !!}"),
     
    (r"c:\xampp\htdocs\iseki_marshalling\resources\views\member\record\record-scan.blade.php",
     r"\{\{ \$recordList->Difference \}\}",
     r"{!! str_replace(';', '<br>', $recordList->Difference) !!}")
]

for file_path, search_pattern, replacement in files:
    if os.path.exists(file_path):
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        new_content = re.sub(search_pattern, replacement, content)
        
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Updated {file_path}")
    else:
        print(f"File not found: {file_path}")

# For report-empty.blade.php
file_path = r"c:\xampp\htdocs\iseki_marshalling\resources\views\admin\records\report-empty.blade.php"
if os.path.exists(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Needs special care due to escHtml
    search = r"escHtml\(item\.Difference \|\| '-'\)"
    repl = r"(item.Difference ? escHtml(item.Difference).replace(/;/g, '<br>') : '-')"
    new_content = re.sub(search, repl, content)
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print(f"Updated {file_path}")
