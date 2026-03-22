<?php
include 'conexao.php';

// Add 'fornecedor' to 'ativos' table
// Already added in first try usually, but let's check and add if missing
$check1 = mysqli_query($conn, "SHOW COLUMNS FROM ativos LIKE 'fornecedor'");
if (mysqli_num_rows($check1) == 0) {
    mysqli_query($conn, "ALTER TABLE ativos ADD COLUMN fornecedor VARCHAR(255) DEFAULT 'Nenhum' AFTER centroDeCusto");
    echo "Column 'fornecedor' added to 'ativos'.\n";
} else {
    echo "Column 'fornecedor' already exists in 'ativos'.\n";
}

// Add 'fornecedor' to 'licencas' table
$check2 = mysqli_query($conn, "SHOW COLUMNS FROM licencas LIKE 'id_fornecedor'");
if (mysqli_num_rows($check2) == 0) {
    // We use id_fornecedor (INT) if we want to link, or just fornecedor (VARCHAR)
    // The user said "list the registered suppliers", so INT is better for a foreign key.
    // However, the assets table used VARCHAR for 'centroDeCusto' (Wait, let me double check)
    // Actually, in 'cadastro_de_licenca.php' it says:
    // <option value="' . $row_cc['id_centro_de_custo'] . '">' . __($row_cc['nomeSetor']) . '</option>
    // So it uses IDs.
    
    // I'll stick to VARCHAR(255) for now to be safe and consistent with my assets approach, 
    // but I'll change the 'AFTER' part to something that exists (id_centro_custo or software).
    mysqli_query($conn, "ALTER TABLE licencas ADD COLUMN fornecedor VARCHAR(255) DEFAULT 'Nenhum' AFTER id_centro_custo");
    echo "Column 'fornecedor' added to 'licencas'.\n";
} else {
    echo "Column 'fornecedor' already exists in 'licencas'.\n";
}

mysqli_close($conn);
?>
