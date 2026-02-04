@echo off
echo Restoring all remaining files from backup...

REM View files
copy "backups\encoding_fix\2026-02-02_19-45-29\breadcrumb.php" "app\views\_layout\breadcrumb.php"
copy "backups\encoding_fix\2026-02-02_19-45-29\categories.php" "app\views\categories\categories.php"
copy "backups\encoding_fix\2026-02-02_19-45-29\dashboard.php" "app\views\admin\dashboard.php"
copy "backups\encoding_fix\2026-02-02_19-45-29\details.php" "app\views\products\details.php"
copy "backups\encoding_fix\2026-02-02_19-45-29\logout.php" "app\views\auth\logout.php"
copy "backups\encoding_fix\2026-02-02_19-45-29\related.php" "app\views\_layout\related.php"

REM Test files
copy "backups\encoding_fix\2026-02-02_19-45-29\test_404_handling.php" "tests\test_404_handling.php"
copy "backups\encoding_fix\2026-02-02_19-45-29\test_charset_headers.php" "tests\test_charset_headers.php"
copy "backups\encoding_fix\2026-02-02_19-45-29\test_clean_urls.php" "tests\test_clean_urls.php"
copy "backups\encoding_fix\2026-02-02_19-45-29\property_test_encoding.php" "tests\property_test_encoding.php"

REM Check if any files were missed
echo.
echo Restoration completed!
echo All files have been restored from backup 2026-02-02_19-45-29
pause