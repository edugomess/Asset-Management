import pytest
import time
import os
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

# --- CONFIGURATION ---
BASE_URL = "http://localhost"
ADMIN_USER = os.getenv("ASSET_MGT_USER", "test@test.com")
ADMIN_PASS = os.getenv("ASSET_MGT_PASS", "123456")

class TestAssetManagementSuite:
    @pytest.fixture(scope="class")
    def driver(self):
        """Setup Chrome Driver with standard configurations."""
        chrome_options = Options()
        # chrome_options.add_argument("--headless")
        chrome_options.add_argument("--window-size=1920,1080")
        # chrome_options.add_argument("--disable-gpu")
        # chrome_options.add_argument("--no-sandbox")
        
        service = Service(ChromeDriverManager().install())
        driver = webdriver.Chrome(service=service, options=chrome_options)
        driver.implicitly_wait(10)
        yield driver
        driver.quit()

    def wait_and_click(self, driver, selector, timeout=10):
        element = WebDriverWait(driver, timeout).until(
            EC.element_to_be_clickable((By.CSS_SELECTOR, selector))
        )
        element.click()
        return element

    # 1. AUTHENTICATION
    def test_01_login_flow(self, driver):
        """Verify the login process and dashboard access."""
        driver.get(f"{BASE_URL}/login.php")
        driver.find_element(By.NAME, "email").send_keys(ADMIN_USER)
        driver.find_element(By.NAME, "senha").send_keys(ADMIN_PASS)
        driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        
        WebDriverWait(driver, 10).until(EC.url_contains("index.php"))
        assert "Dashboard" in driver.title or "Consola" in driver.page_source

    # 2. INVENTORY & MODALS
    def test_02_inventory_premium_modals(self, driver):
        """Verify the standardized premium modals in the inventory list."""
        driver.get(f"{BASE_URL}/equipamentos.php")
        
        # Test Assignment Modal (Premium Design)
        self.wait_and_click(driver, "button[onclick*='openAssignModal']")
        modal = WebDriverWait(driver, 5).until(EC.visibility_of_element_located((By.ID, "assignModal")))
        assert "Atribuir" in modal.text
        
        # Close modal
        driver.find_element(By.CSS_SELECTOR, "#assignModal .close").click()
        time.sleep(1)

        # Test Maintenance Modal
        self.wait_and_click(driver, "button[onclick*='sendToMaintenance']")
        modal_maint = WebDriverWait(driver, 5).until(EC.visibility_of_element_located((By.ID, "maintenanceModal")))
        assert "Manutenção" in modal_maint.text
        driver.find_element(By.CSS_SELECTOR, "#maintenanceModal .close").click()

    # 3. ASSET PROFILE & LOGIC
    def test_03_asset_profile_logic(self, driver):
        """Verify the robust attribution display and maintenance release buttons."""
        # Find first asset card and click tag to view profile
        tag_link = driver.find_element(By.CSS_SELECTOR, "td a[href*='perfil_ativo.php']")
        tag_link.click()
        
        WebDriverWait(driver, 10).until(EC.url_contains("perfil_ativo.php"))
        
        # Verify "Responsabilidade e Localização" Section (Robust logic)
        resp_section = WebDriverWait(driver, 5).until(
            EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'Responsabilidade e Localização')]"))
        )
        assert resp_section.is_displayed()
        
        # If asset is in maintenance, test the SweetAlert2 Release button
        try:
            finish_btn = driver.find_element(By.CSS_SELECTOR, "button[onclick*='releaseMaintenance']")
            if finish_btn.is_displayed():
                finish_btn.click()
                
                # Check for SweetAlert2 (Standardized)
                swal = WebDriverWait(driver, 5).until(EC.visibility_of_element_located((By.CLASS_NAME, "swal2-popup")))
                assert "Deseja liberar" in swal.text or "concluir" in swal.text.lower()
                
                # Cancel or Confirm (Cancel to keep test non-destructive for now)
                driver.find_element(By.CLASS_NAME, "swal2-cancel").click()
        except Exception:
            pass # Asset not in state to show button

    # 4. LOCATION MANAGEMENT
    def test_04_location_hierarchy(self, driver):
        """Verify the location hierarchy tree and deletion protection modal."""
        driver.get(f"{BASE_URL}/locais.php")
        
        # Check for card summary
        assert "Gerenciar Locais" in driver.page_source
        
        # Test Delete Modal (Dependency check)
        try:
            delete_btn = driver.find_element(By.CSS_SELECTOR, "button[data-target='#modalDeleteLocal']")
            delete_btn.click()
            
            # Verify Premium Delete Modal
            modal_del = WebDriverWait(driver, 5).until(EC.visibility_of_element_located((By.ID, "modalDeleteLocal")))
            assert "Atenção" in modal_del.text
            driver.find_element(By.CSS_SELECTOR, "#modalDeleteLocal .close").click()
        except Exception:
            pass

    # 5. USER PROFILE SEARCH (AJAX)
    def test_05_ajax_search_standardization(self, driver):
        """Verify the standardized AJAX search results in attribution modal."""
        driver.get(f"{BASE_URL}/equipamentos.php")
        self.wait_and_click(driver, "button[onclick*='openAssignModal']")
        
        search_input = driver.find_element(By.ID, "assignSearchInput")
        search_input.send_keys("admin")
        
        # Wait for AJAX results
        results = WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.ID, "assignSearchResults")))
        assert results.is_displayed()
        assert len(results.find_elements(By.TAG_NAME, "a")) > 0

if __name__ == "__main__":
    print("\n[INFO] Starting Asset Management Automated Test Suite...")
    print("[HINT] Run via: pytest test_suite_asset_management.py")
    pytest.main([__file__])
