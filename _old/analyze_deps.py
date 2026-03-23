import os
import re

root_dir = r'c:\xampp\htdocs'
all_files = []

for root, dirs, files in os.walk(root_dir):
    # Exclude .git and vendor if they exist (though vendor wasn't in list_dir)
    if '.git' in dirs:
        dirs.remove('.git')
    for file in files:
        rel_path = os.path.relpath(os.path.join(root, file), root_dir)
        all_files.append(rel_path)

results = {}

# We only care about .php, .js, .html, .css for analysis
code_extensions = ('.php', '.js', '.html', '.css')
analysis_files = [f for f in all_files if f.endswith(code_extensions)]

print(f"Analyzing {len(analysis_files)} code files out of {len(all_files)} total files...")

# Optimization: Read all file contents into memory once
file_contents = {}
for f in analysis_files:
    try:
        with open(os.path.join(root_dir, f), 'r', encoding='utf-8', errors='ignore') as content_file:
            file_contents[f] = content_file.read()
    except Exception as e:
        print(f"Could not read {f}: {e}")

# Files that are definitely needed (Entry points)
entry_points = [
    'login.php', 'index.php', 'inicio.php', 'usuarios.php', 'equipamentos.php', 
    'chamados.php', 'licencas.php', 'fornecedores.php', 'centro_de_custo.php', 
    'relatorios.php', 'configuracoes.php', 'documentacao.php', 'suporte.php',
    'perfil_usuario.php', 'perfil_centro_de_custo.php', 'perfil_fornecedor.php',
    'perfil_licenca.php', 'perfil_ativo.php', 'logout.php', 'auth.php', 'conexao.php',
    'config_notificacoes.php', 'language.php'
]

unused_candidates = []

for f in analysis_files:
    if f in entry_points:
        continue
    
    basename = os.path.basename(f)
    # Skip icons, maps, etc if any
    if basename.endswith(('.map', '.ico')):
        continue
        
    found = False
    for other_f, content in file_contents.items():
        if f == other_f:
            continue
        # Search for basename in content. 
        # Using regex to ensure we catch 'filename', "filename", filename.php (without extension in some AJAX?)
        # Simple string search first
        if basename in content:
            found = True
            break
            
    if not found:
        # Also check for basename without extension (common in AJAX or links)
        basename_no_ext = os.path.splitext(basename)[0]
        if len(basename_no_ext) > 3: # Avoid false positives with short names
            for other_f, content in file_contents.items():
                if f == other_f:
                    continue
                if basename_no_ext in content:
                    found = True
                    break
                    
    if not found:
        unused_candidates.append(f)

print("\n--- POSSÍVEIS ARQUIVOS INÚTEIS (SEM REFERÊNCIAS NO CÓDIGO) ---")
for f in sorted(unused_candidates):
    print(f)

# Categorize suspicious files based on name patterns
print("\n--- ARQUIVOS SUSPEITOS (PADRÃO DE NOME: test, debug, temp, backup, migrar, setup) ---")
suspicious_patterns = ['test', 'debug', 'temp', 'backup', 'migrar', 'setup', 'alter_db', 'check_', 'diag_']
for f in analysis_files:
    basename = os.path.basename(f).lower()
    if any(p in basename for p in suspicious_patterns):
        if f not in unused_candidates:
             print(f"{f} (Referenciado, mas padrão de nome suspeito)")
        else:
             print(f"{f} (SEM REFERÊNCIA + padrão de nome suspeito)")
