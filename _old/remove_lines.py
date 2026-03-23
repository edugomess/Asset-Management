
try:
    with open("c:/xampp/htdocs/index.php", "r", encoding="utf-8") as f:
        lines = f.readlines()
    
    # Lines 410 to 462 (1-based) corresponds to indices 409 to 462 (slice)
    # verify content roughly
    if "<!-- Start: SLA Ranking -->" in lines[409] and "<!-- End: SLA Ranking -->" in lines[461]:
        print("Found matching lines. Removing...")
        del lines[409:462]
        
        with open("c:/xampp/htdocs/index.php", "w", encoding="utf-8") as f:
            f.writelines(lines)
        print("Successfully removed lines.")
    else:
        print("Line content mismatch. Aborting.")
        print(f"Line 410: {lines[409]}")
        print(f"Line 462: {lines[461]}")

except Exception as e:
    print(f"Error: {e}")
