$ErrorActionPreference = 'Stop'
$base = 'http://127.0.0.1:8080'
$mysql = 'C:\xampp\mysql\bin\mysql.exe'
$script:passCount = 0
$script:failCount = 0

function Assert($condition, $label) {
    if ($condition) {
        $script:passCount++
        Write-Output "PASS  $label"
    } else {
        $script:failCount++
        Write-Output "FAIL  $label"
    }
}

function Invoke-App {
    param(
        [string]$Uri,
        $Session,
        [string]$Method = 'GET',
        $Body = $null
    )
    $params = @{
        Uri = $Uri
        Method = $Method
        UseBasicParsing = $true
        ErrorAction = 'Stop'
    }
    if ($Session -ne $null) { $params.WebSession = $Session }
    if ($Body -ne $null) { $params.Body = $Body }

    try {
        $response = Invoke-WebRequest @params
        return @{
            Status = [int]$response.StatusCode
            Content = [string]$response.Content
            FinalUri = $response.BaseResponse.ResponseUri.AbsoluteUri
        }
    } catch {
        $httpResponse = $_.Exception.Response
        if ($httpResponse -ne $null) {
            $content = ''
            try {
                $stream = $httpResponse.GetResponseStream()
                if ($stream -ne $null) {
                    $reader = New-Object System.IO.StreamReader($stream)
                    $content = $reader.ReadToEnd()
                    $reader.Close()
                }
            } catch { }
            return @{
                Status = [int]$httpResponse.StatusCode
                Content = $content
                FinalUri = ''
            }
        }
        return @{ Status = 0; Content = $_.Exception.Message; FinalUri = '' }
    }
}

function Get-CsrfToken($content) {
    $match = [regex]::Match([string]$content, 'name="csrf_token" value="([a-f0-9]+)"')
    if ($match.Success) { return $match.Groups[1].Value }
    return ''
}

function Get-Sql($query) {
    $output = & $mysql -u root msu_share_care -N -e $query 2>$null
    return ("$output" -replace "`r`n", '').Trim()
}

Write-Output '=== Setup ==='

& $mysql -u root msu_share_care -e 'DELETE FROM items; DELETE FROM users; ALTER TABLE users AUTO_INCREMENT = 1; ALTER TABLE items AUTO_INCREMENT = 1;'

$sA = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$sB = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$sG = New-Object Microsoft.PowerShell.Commands.WebRequestSession

$r = Invoke-App "$base/register.php" $sA
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/register.php" $sA -Method POST -Body @{
    csrf_token = $t
    full_name = 'Test Admin A'
    email = 'studenta@example.com'
    password = 'TestPass123'
    password_confirm = 'TestPass123'
    contact_info = '081-234-5678'
}
Assert ($r.Content -match 'nav-user') 'Setup: register user A'

& $mysql -u root msu_share_care -e "UPDATE users SET role = 'admin' WHERE email = 'studenta@example.com';"
Assert ((Get-Sql "SELECT role FROM users WHERE email = 'studenta@example.com'") -eq 'admin') 'Setup: A promoted to admin'

$r = Invoke-App "$base/register.php" $sB
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/register.php" $sB -Method POST -Body @{
    csrf_token = $t
    full_name = 'Test Student B'
    email = 'studentb@example.com'
    password = 'TestPass123'
    password_confirm = 'TestPass123'
    contact_info = '089-999-9999'
}
Assert ($r.Content -match 'nav-user') 'Setup: register user B'

$r = Invoke-App "$base/item_create.php" $sB
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/item_create.php" $sB -Method POST -Body @{
    csrf_token = $t
    title = 'โต๊ะพับ ไม้เก่า'
    description = 'โต๊ะพับสภาพใช้ได้'
    type = 'donate'
    contact = '089-999-9999'
}
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '1') 'Setup: B creates one item'

Write-Output '=== Admin pages (admin A) ==='

$r = Invoke-App "$base/admin/dashboard.php" $sA
Assert ($r.Status -eq 200) 'Admin GET dashboard -> 200'
Assert ($r.Content.Contains('stat-number">2</p>')) 'Dashboard: user count = 2'
Assert ($r.Content.Contains('stat-number">1</p>')) 'Dashboard: item count = 1'
Assert ($r.Content.Contains('stat-number">0</p>')) 'Dashboard: completed count = 0'
Assert ($r.Content.Contains('../assets/css/style.css')) 'Dashboard: CSS path uses base'
Assert ($r.Content.Contains('../index.php')) 'Dashboard: nav link uses base'

$r = Invoke-App "$base/admin/users.php" $sA
Assert ($r.Status -eq 200) 'Admin GET users -> 200'
Assert ($r.Content.Contains('studenta@example.com')) 'Users list shows A'
Assert ($r.Content.Contains('studentb@example.com')) 'Users list shows B'
Assert ($r.Content.Contains('badge-admin')) 'Users list shows admin badge'

$r = Invoke-App "$base/admin/items.php" $sA
Assert ($r.Status -eq 200) 'Admin GET items -> 200'
Assert ($r.Content.Contains('โต๊ะพับ ไม้เก่า')) 'Items list shows B item'
Assert ($r.Content.Contains('Test Student B')) 'Items list shows owner name'

$r = Invoke-App "$base/index.php" $sA
Assert ($r.Content.Contains('admin/dashboard.php')) 'Nav shows Admin link for admin on root page'

Write-Output '=== Case D: non-admin denied ==='

$r = Invoke-App "$base/admin/dashboard.php" $sB
Assert ($r.Status -eq 403) 'B GET admin dashboard -> 403'
$r = Invoke-App "$base/admin/users.php" $sB
Assert ($r.Status -eq 403) 'B GET admin users -> 403'
$r = Invoke-App "$base/admin/items.php" $sB
Assert ($r.Status -eq 403) 'B GET admin items -> 403'

$r = Invoke-App "$base/admin/dashboard.php" $sB
Assert ($r.Status -eq 403 -and -not ($r.Content.Contains('studentb@example.com'))) '403 page leaks no user data'

$r = Invoke-App "$base/admin/dashboard.php" $sG
Assert ($r.FinalUri -match 'login\.php') 'Guest GET admin dashboard -> redirected to login'

$r = Invoke-App "$base/index.php" $sB
Assert (-not ($r.Content.Contains('admin/dashboard.php'))) 'Nav hides Admin link for normal user'

Write-Output '=== Admin delete item ==='

$r = Invoke-App "$base/admin/items.php" $sB -Method POST -Body @{ delete_id = '1' }
Assert ($r.Status -eq 403) 'B POST admin delete -> 403'
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '1') 'Item survives B admin delete attempt'

$r = Invoke-App "$base/admin/items.php" $sA -Method POST -Body @{ delete_id = '1' }
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '1') 'Item survives no-CSRF admin delete attempt'

$r = Invoke-App "$base/item_create.php" $sB
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/item_create.php" $sB -Method POST -Body @{
    csrf_token = $t
    title = 'เก้าอี้พลาสติก'
    description = 'สภาพดี'
    type = 'exchange'
    contact = '089-999-9999'
}
$adminItemId = 0
if ($r.FinalUri -match 'id=(\d+)') { $adminItemId = [int]$Matches[1] }
Assert ($adminItemId -gt 0) 'Setup: second item created'

$r = Invoke-App "$base/admin/items.php" $sA
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/admin/items.php" $sA -Method POST -Body @{ csrf_token = $t; delete_id = [string]$adminItemId }
Assert ((Get-Sql "SELECT COUNT(*) FROM items WHERE id = $adminItemId") -eq '0') 'Admin deletes target item with valid CSRF'
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '1') 'First item still exists'

$r = Invoke-App "$base/admin/items.php" $sA
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/admin/items.php" $sA -Method POST -Body @{ csrf_token = $t; delete_id = '1' }
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '0') 'Admin deletes remaining item'

$r = Invoke-App "$base/admin/items.php" $sA
Assert ($r.Content -match 'empty-state') 'Admin items list shows empty state'

Write-Output ''
Write-Output "RESULT: PASS=$script:passCount FAIL=$script:failCount"
if ($script:failCount -gt 0) { exit 1 }
exit 0
