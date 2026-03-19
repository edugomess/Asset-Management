import os
import re

directory = r'c:\xampp\htdocs'
replacement = "<?php include 'topbar.php'; ?>"
pattern = re.compile(r'<nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar.*?</nav>', re.DOTALL)

for root, _, files in os.walk(directory):
    for filename in files:
        if filename.endswith(".php") and filename != "topbar.php":
            filepath = os.path.join(root, filename)
            with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
            
            new_content, count = pattern.subn(replacement, content)
            
            if count > 0:
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                print(f"Replaced topbar in {filepath}")

print("Done replacing topbar.")
