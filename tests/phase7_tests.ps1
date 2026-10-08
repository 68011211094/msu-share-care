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
$sV = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$sR = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$sX = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$sL = New-Object Microsoft.PowerShell.Commands.WebRequestSession

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
Assert ($r.Content -match 'nav-user') 'Setup: register A'

& $mysql -u root msu_share_care -e "UPDATE users SET role = 'admin' WHERE email = 'studenta@example.com';"
Assert ((Get-Sql "SELECT role FROM users WHERE email = 'studenta@example.com'") -eq 'admin') 'Setup: A promoted to admin'

$r = Invoke-App "$base/register.php" $sB
$tB = Get-CsrfToken $r.Content
$r = Invoke-App "$base/register.php" $sB -Method POST -Body @{
    csrf_token = $tB
    full_name = 'Test Student B'
    email = 'studentb@example.com'
    password = 'TestPass123'
    password_confirm = 'TestPass123'
    contact_info = '089-999-9999'
}
Assert ($r.Content -match 'nav-user') 'Setup: register B'

$r = Invoke-App "$base/item_create.php" $sA
$tA = Get-CsrfToken $r.Content
$r = Invoke-App "$base/item_create.php" $sA -Method POST -Body @{
    csrf_token = $tA
    title = [string]'ลำโพงบลูทูธเก่า'
    description = [string]'ใช้งานได้ปกติ พร้อมสายชาร์จ'
    type = 'donate'
    contact = '081-234-5678'
}
$idA = 0
if ($r.FinalUri -match 'id=(\d+)') { $idA = [int]$Matches[1] }
Assert ($idA -gt 0) 'Setup: A creates item A1'

$r = Invoke-App "$base/item_create.php" $sB
$r = Invoke-App "$base/item_create.php" $sB -Method POST -Body @{
    csrf_token = $tB
    title = [string]'เก้าอี้พลาสติก'
    description = [string]'สภาพดี'
    type = 'exchange'
    contact = '089-999-9999'
}
$idB = 0
if ($r.FinalUri -match 'id=(\d+)') { $idB = [int]$Matches[1] }
Assert ($idB -gt 0) 'Setup: B creates item B1'

Assert ((Get-Sql 'SELECT COUNT(*) FROM users') -eq '2') 'Setup: users = 2'
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '2') 'Setup: items = 2'

Write-Output '=== Register validation (invalid input) ==='

$r = Invoke-App "$base/register.php" $sV
$tV = Get-CsrfToken $r.Content

$r = Invoke-App "$base/register.php" $sV -Method POST -Body @{
    csrf_token = $tV
    full_name = ''
    email = ''
    password = ''
    password_confirm = ''
}
Assert ($r.Content.Contains([string]'กรุณากรอกชื่อ-นามสกุล')) 'Register: empty name rejected'
Assert ($r.Content.Contains([string]'กรุณากรอกอีเมล')) 'Register: empty email rejected'
Assert ($r.Content.Contains([string]'กรุณากรอกรหัสผ่าน')) 'Register: empty password rejected'

$r = Invoke-App "$base/register.php" $sV -Method POST -Body @{
    csrf_token = $tV
    full_name = 'V Test'
    email = 'notanemail'
    password = 'TestPass123'
    password_confirm = 'TestPass123'
}
Assert ($r.Content.Contains([string]'รูปแบบอีเมลไม่ถูกต้อง')) 'Register: invalid email rejected'

$r = Invoke-App "$base/register.php" $sV -Method POST -Body @{
    csrf_token = $tV
    full_name = 'V Test'
    email = 'v1@example.com'
    password = 'short'
    password_confirm = 'short'
}
Assert ($r.Content.Contains([string]'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร')) 'Register: short password rejected'

$r = Invoke-App "$base/register.php" $sV -Method POST -Body @{
    csrf_token = $tV
    full_name = 'V Test'
    email = 'v1@example.com'
    password = 'TestPass123'
    password_confirm = 'TestPass999'
}
Assert ($r.Content.Contains([string]'รหัสผ่านยืนยันไม่ตรงกัน')) 'Register: password mismatch rejected'

$r = Invoke-App "$base/register.php" $sV -Method POST -Body @{
    csrf_token = $tV
    full_name = 'V Test'
    email = 'studenta@example.com'
    password = 'TestPass123'
    password_confirm = 'TestPass123'
}
Assert ($r.Content.Contains([string]'อีเมลนี้ถูกใช้งานแล้ว')) 'Register: duplicate email rejected'

$longName = ('x' * 121) -join ''
$r = Invoke-App "$base/register.php" $sV -Method POST -Body @{
    csrf_token = $tV
    full_name = $longName
    email = 'v2@example.com'
    password = 'TestPass123'
    password_confirm = 'TestPass123'
}
Assert ($r.Content.Contains([string]'ชื่อ-นามสกุลต้องไม่เกิน 100 ตัวอักษร')) 'Register: 121-char name rejected'

$r = Invoke-App "$base/register.php" $sV -Method POST -Body @{
    full_name = 'No CSRF'
    email = 'v3@example.com'
    password = 'TestPass123'
    password_confirm = 'TestPass123'
}
Assert ($r.Content.Contains([string]'เซสชันหมดอายุ')) 'Register: no-CSRF rejected'
Assert ((Get-Sql 'SELECT COUNT(*) FROM users') -eq '2') 'Register: invalid inputs create no users'

Write-Output '=== Role escalation attempt ==='

$r = Invoke-App "$base/register.php" $sR
$tR = Get-CsrfToken $r.Content
$r = Invoke-App "$base/register.php" $sR -Method POST -Body @{
    csrf_token = $tR
    role = 'admin'
    full_name = 'Role Test'
    email = 'roletest@example.com'
    password = 'TestPass123'
    password_confirm = 'TestPass123'
}
Assert ($r.Content -match 'nav-user') 'Register: role escalation user created'
Assert ((Get-Sql 'SELECT COUNT(*) FROM users') -eq '3') 'Register: users = 3 after role attempt'
Assert ((Get-Sql "SELECT role FROM users WHERE email = 'roletest@example.com'") -eq 'user') 'Register: injected role ignored, role stays user'

Write-Output '=== XSS in registered name ==='

$r = Invoke-App "$base/register.php" $sX
$tX = Get-CsrfToken $r.Content
$r = Invoke-App "$base/register.php" $sX -Method POST -Body @{
    csrf_token = $tX
    full_name = '<script>alert(1)</script>'
    email = 'xssname@example.com'
    password = 'TestPass123'
    password_confirm = 'TestPass123'
}
Assert ($r.Content -match 'nav-user') 'Register: XSS name user created'
Assert ((Get-Sql 'SELECT COUNT(*) FROM users') -eq '4') 'Register: users = 4'

$r = Invoke-App "$base/index.php" $sX
Assert ($r.Content.Contains('&lt;script&gt;alert(1)&lt;/script&gt;')) 'XSS name: escaped on page'
Assert (-not ($r.Content.Contains('<script>alert(1)</script>'))) 'XSS name: raw script not rendered'

Write-Output '=== Login validation ==='

$r = Invoke-App "$base/login.php" $sV
$tV = Get-CsrfToken $r.Content

$r = Invoke-App "$base/login.php" $sV -Method POST -Body @{
    csrf_token = $tV
    email = 'studenta@example.com'
    password = 'WrongPass999'
}
Assert ($r.Content.Contains([string]'อีเมลหรือรหัสผ่านไม่ถูกต้อง')) 'Login: wrong password rejected'
Assert (-not ($r.Content.Contains('nav-user'))) 'Login: wrong password not logged in'

$r = Invoke-App "$base/login.php" $sV -Method POST -Body @{
    csrf_token = $tV
    email = 'nobody@example.com'
    password = 'Whatever123'
}
Assert ($r.Content.Contains([string]'อีเมลหรือรหัสผ่านไม่ถูกต้อง')) 'Login: unknown email same generic message'

$r = Invoke-App "$base/login.php" $sV -Method POST -Body @{
    csrf_token = $tV
    email = ''
    password = ''
}
Assert ($r.Content.Contains([string]'กรุณากรอกอีเมล')) 'Login: empty email rejected'

$r = Invoke-App "$base/login.php" $sV -Method POST -Body @{
    csrf_token = $tV
    email = "' OR '1'='1"
    password = 'anything'
}
Assert ($r.Content.Contains([string]'อีเมลหรือรหัสผ่านไม่ถูกต้อง')) 'Login: SQLi email rejected'
Assert (-not ($r.Content.Contains('nav-user'))) 'Login: SQLi no bypass'

$r = Invoke-App "$base/login.php" $sV -Method POST -Body @{
    email = 'studenta@example.com'
    password = 'TestPass123'
}
Assert ($r.Content.Contains([string]'เซสชันหมดอายุ')) 'Login: no-CSRF shows error'
Assert (-not ($r.Content.Contains('nav-user'))) 'Login: no-CSRF not logged in despite correct password'

Write-Output '=== Guest access control ==='

$r = Invoke-App "$base/my_items.php" $sG
Assert ($r.FinalUri -match 'login\.php') 'Guest GET my_items -> login'

$r = Invoke-App "$base/item_create.php" $sG
Assert ($r.FinalUri -match 'login\.php') 'Guest GET item_create -> login'

$r = Invoke-App "$base/item_edit.php?id=$idA" $sG
Assert ($r.FinalUri -match 'login\.php') 'Guest GET item_edit -> login'

$r = Invoke-App "$base/item_delete.php?id=$idA" $sG -Method POST -Body @{ dummy = '1' }
Assert ($r.FinalUri -match 'login\.php') 'Guest POST item_delete -> login'
Assert ((Get-Sql "SELECT COUNT(*) FROM items WHERE id = $idA") -eq '1') 'Guest delete: item survives'

$r = Invoke-App "$base/item_complete.php?id=$idA" $sG -Method POST -Body @{ dummy = '1' }
Assert ($r.FinalUri -match 'login\.php') 'Guest POST item_complete -> login'
Assert ((Get-Sql "SELECT status FROM items WHERE id = $idA") -eq 'available') 'Guest complete: status unchanged'

$r = Invoke-App "$base/item_create.php" $sG -Method POST -Body @{ dummy = '1' }
Assert ($r.FinalUri -match 'login\.php') 'Guest POST item_create -> login'
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '2') 'Guest create: no item created'

Write-Output '=== Logout and CSRF on every endpoint ==='

$r = Invoke-App "$base/login.php" $sL
$tL = Get-CsrfToken $r.Content
$r = Invoke-App "$base/login.php" $sL -Method POST -Body @{
    csrf_token = $tL
    email = 'studentb@example.com'
    password = 'TestPass123'
}
Assert ($r.Content -match 'nav-user') 'Setup: logout session logged in as B'

$r = Invoke-App "$base/logout.php" $sL
Assert ($r.FinalUri -match 'index\.php') 'Logout GET: redirected but no action'
$r = Invoke-App "$base/my_items.php" $sL
Assert ($r.Content -match 'nav-user') 'Logout GET: session survives'

$r = Invoke-App "$base/logout.php" $sL -Method POST -Body @{ dummy = '1' }
Assert ($r.FinalUri -match 'index\.php') 'Logout no-CSRF: redirected but no action'
$r = Invoke-App "$base/my_items.php" $sL
Assert ($r.Content -match 'nav-user') 'Logout no-CSRF: session survives'

$r = Invoke-App "$base/logout.php" $sL -Method POST -Body @{ csrf_token = $tL }
Assert ($r.FinalUri -match 'index\.php') 'Logout with CSRF: redirected'
$r = Invoke-App "$base/my_items.php" $sL
Assert ($r.FinalUri -match 'login\.php') 'Logout with CSRF: session destroyed'

$r = Invoke-App "$base/item_create.php" $sB -Method POST -Body @{
    title = [string]'ไม่มี CSRF'
    description = [string]'x'
    type = 'donate'
    contact = '089'
}
Assert ($r.Content.Contains([string]'เซสชันหมดอายุ')) 'item_create no-CSRF: error shown'
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '2') 'item_create no-CSRF: no item created'

$r = Invoke-App "$base/item_edit.php?id=$idB" $sB -Method POST -Body @{
    title = [string]'ถูกแฮก'
    description = [string]'x'
    type = 'donate'
    contact = '089'
}
Assert ($r.Content.Contains([string]'เซสชันหมดอายุ')) 'item_edit no-CSRF: error shown'
$r = Invoke-App "$base/item_detail.php?id=$idB" $sG
Assert (-not ($r.Content.Contains('ถูกแฮก'))) 'item_edit no-CSRF: DB unchanged'

$r = Invoke-App "$base/item_delete.php?id=$idB" $sB -Method POST -Body @{ dummy = '1' }
Assert ($r.FinalUri -match 'my_items\.php') 'item_delete no-CSRF: no action'
Assert ((Get-Sql "SELECT COUNT(*) FROM items WHERE id = $idB") -eq '1') 'item_delete no-CSRF: item survives'

$r = Invoke-App "$base/item_complete.php?id=$idB" $sB -Method POST -Body @{ dummy = '1' }
Assert ($r.FinalUri -match 'my_items\.php') 'item_complete no-CSRF: no action'
Assert ((Get-Sql "SELECT status FROM items WHERE id = $idB") -eq 'available') 'item_complete no-CSRF: status unchanged'

$fakeToken = ('a' * 64) -join ''
$r = Invoke-App "$base/item_delete.php?id=$idB" $sB -Method POST -Body @{ csrf_token = $fakeToken }
Assert ($r.FinalUri -match 'my_items\.php') 'item_delete tampered token: no action'
Assert ((Get-Sql "SELECT COUNT(*) FROM items WHERE id = $idB") -eq '1') 'item_delete tampered token: item survives'

$r = Invoke-App "$base/admin/items.php" $sA -Method POST -Body @{ delete_id = [string]$idB }
Assert ((Get-Sql "SELECT COUNT(*) FROM items WHERE id = $idB") -eq '1') 'Admin delete no-CSRF: item survives'

Write-Output '=== Ownership matrix (Case A / B / C) ==='

$r = Invoke-App "$base/item_edit.php?id=$idA" $sA
Assert ($r.Status -eq 200) 'Case A: A GET own edit -> 200'

$r = Invoke-App "$base/item_edit.php?id=$idA" $sA -Method POST -Body @{
    csrf_token = $tA
    title = [string]'ลำโพงบลูทูธเก่า (แก้ไขแล้ว)'
    description = [string]'ใช้งานได้ปกติ พร้อมสายชาร์จ'
    type = 'donate'
    contact = '081-234-5678'
}
$r = Invoke-App "$base/item_detail.php?id=$idA" $sG
Assert ($r.Content.Contains('แก้ไขแล้ว')) 'Case A: A edit own item applied'

$r = Invoke-App "$base/item_detail.php?id=$idA" $sB
Assert ($r.Status -eq 200) 'Case B: B views A item -> 200'
Assert (-not ($r.Content.Contains('item_edit.php?id='))) 'Case B: B sees no edit control'
Assert (-not ($r.Content.Contains('item_delete.php'))) 'Case B: B sees no delete control'

$r = Invoke-App "$base/item_edit.php?id=$idA" $sB
Assert ($r.Status -eq 403) 'Case C: B GET A edit -> 403'

$r = Invoke-App "$base/item_edit.php?id=$idA" $sB -Method POST -Body @{
    csrf_token = $tB
    title = [string]'B พยายามแก้'
    description = [string]'x'
    type = 'donate'
    contact = 'x'
}
Assert ($r.Status -eq 403) 'Case C: B POST A edit -> 403'
$r = Invoke-App "$base/item_detail.php?id=$idA" $sG
Assert (-not ($r.Content.Contains('B พยายามแก้'))) 'Case C: A item unchanged after B edit attempt'
Assert ($r.Content.Contains('แก้ไขแล้ว')) 'Case C: A title intact'

$r = Invoke-App "$base/item_delete.php?id=$idA" $sB -Method POST -Body @{ csrf_token = $tB }
Assert ($r.Status -eq 403) 'Case C: B POST A delete -> 403'
Assert ((Get-Sql "SELECT COUNT(*) FROM items WHERE id = $idA") -eq '1') 'Case C: A item survives'

$r = Invoke-App "$base/item_complete.php?id=$idA" $sB -Method POST -Body @{ csrf_token = $tB }
Assert ($r.Status -eq 403) 'Case C: B POST A complete -> 403'
Assert ((Get-Sql "SELECT status FROM items WHERE id = $idA") -eq 'available') 'Case C: A status unchanged'

$r = Invoke-App "$base/item_edit.php?id=$idB" $sA
Assert ($r.Status -eq 403) 'Admin: A GET edit B item -> 403'

$r = Invoke-App "$base/item_edit.php?id=$idB" $sA -Method POST -Body @{
    csrf_token = $tA
    title = [string]'Admin แก้ไขแทน'
    description = [string]'x'
    type = 'donate'
    contact = 'x'
}
Assert ($r.Status -eq 403) 'Admin: A POST edit B item -> 403'
$r = Invoke-App "$base/item_detail.php?id=$idB" $sG
Assert (-not ($r.Content.Contains('Admin แก้ไขแทน'))) 'Admin: B item unchanged after admin edit attempt'

$r = Invoke-App "$base/item_delete.php?id=$idB" $sA -Method POST -Body @{ csrf_token = $tA }
Assert ($r.Status -eq 403) 'Admin: A POST delete B item -> 403'
Assert ((Get-Sql "SELECT COUNT(*) FROM items WHERE id = $idB") -eq '1') 'Admin: B item survives'

$r = Invoke-App "$base/item_complete.php?id=$idB" $sA -Method POST -Body @{ csrf_token = $tA }
Assert ($r.Status -eq 403) 'Admin: A POST complete B item -> 403'
Assert ((Get-Sql "SELECT status FROM items WHERE id = $idB") -eq 'available') 'Admin: B status unchanged'

$r = Invoke-App "$base/item_edit.php?id=$idB" $sB -Method POST -Body @{
    csrf_token = $tB
    title = [string]'เก้าอี้พลาสติก สภาพดี'
    description = [string]'สภาพดี'
    type = 'exchange'
    contact = '089-999-9999'
}
$r = Invoke-App "$base/item_detail.php?id=$idB" $sG
Assert ($r.Content.Contains('เก้าอี้พลาสติก สภาพดี')) 'Case A: B edit own item applied'

$r = Invoke-App "$base/item_edit.php?id=999999" $sA
Assert ($r.Status -eq 404) 'Edit id=999999 -> 404'
$r = Invoke-App "$base/item_edit.php?id=0" $sA
Assert ($r.Status -eq 404) 'Edit id=0 -> 404'
$r = Invoke-App "$base/item_delete.php?id=999999" $sA -Method POST -Body @{ csrf_token = $tA }
Assert ($r.Status -eq 404) 'Delete id=999999 -> 404'
$r = Invoke-App "$base/item_complete.php?id=999999" $sA -Method POST -Body @{ csrf_token = $tA }
Assert ($r.Status -eq 404) 'Complete id=999999 -> 404'

Write-Output '=== Item validation (invalid input by owner) ==='

$r = Invoke-App "$base/item_create.php" $sB -Method POST -Body @{
    csrf_token = $tB
    title = ''
    description = [string]'x'
    type = 'donate'
    contact = '089'
}
Assert ($r.Content.Contains([string]'กรุณากรอกชื่อสิ่งของ')) 'Create: empty title rejected'

$longTitle = ('x' * 121) -join ''
$r = Invoke-App "$base/item_create.php" $sB -Method POST -Body @{
    csrf_token = $tB
    title = $longTitle
    description = [string]'x'
    type = 'donate'
    contact = '089'
}
Assert ($r.Content.Contains([string]'ชื่อสิ่งของต้องไม่เกิน 120 ตัวอักษร')) 'Create: 121-char title rejected'

$r = Invoke-App "$base/item_create.php" $sB -Method POST -Body @{
    csrf_token = $tB
    title = [string]'ทดสอบ'
    description = [string]'x'
    type = 'rent'
    contact = '089'
}
Assert ($r.Content.Contains([string]'ประเภทประกาศไม่ถูกต้อง')) 'Create: invalid type rejected'

$r = Invoke-App "$base/item_create.php" $sB -Method POST -Body @{
    csrf_token = $tB
    title = [string]'ทดสอบ'
    description = [string]'x'
    type = 'donate'
    contact = ''
}
Assert ($r.Content.Contains([string]'กรุณากรอกช่องทางติดต่อ')) 'Create: empty contact rejected'
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '2') 'Create: all invalid inputs create no items'

$r = Invoke-App "$base/item_edit.php?id=$idB" $sB -Method POST -Body @{
    csrf_token = $tB
    title = [string]'เก้าอี้พลาสติก สภาพดี'
    description = ''
    type = 'exchange'
    contact = '089-999-9999'
}
Assert ($r.Content.Contains([string]'กรุณากรอกรายละเอียด')) 'Edit: empty description rejected'

$r = Invoke-App "$base/item_edit.php?id=$idB" $sB -Method POST -Body @{
    csrf_token = $tB
    title = [string]'เก้าอี้พลาสติก สภาพดี'
    description = [string]'สภาพดี'
    type = 'rent'
    contact = '089-999-9999'
}
Assert ($r.Content.Contains([string]'ประเภทประกาศไม่ถูกต้อง')) 'Edit: invalid type rejected'
$r = Invoke-App "$base/item_detail.php?id=$idB" $sG
Assert ($r.Content.Contains('exchange') -or $r.Content.Contains('Exchange')) 'Edit: type unchanged after invalid attempts'
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '2') 'Edit: invalid inputs change no item count'

Write-Output '=== my_items data scoping ==='

$r = Invoke-App "$base/my_items.php" $sB
Assert ($r.Content.Contains('เก้าอี้พลาสติก สภาพดี')) 'my_items: B sees own item'
Assert (-not ($r.Content.Contains('แก้ไขแล้ว'))) 'my_items: B does not see A item'

Write-Output '=== Stored XSS and SQL injection ==='

$r = Invoke-App "$base/item_create.php" $sB -Method POST -Body @{
    csrf_token = $tB
    title = '<img src=x onerror=alert(1)>'
    description = '"><script>alert(2)</script>'
    type = 'donate'
    contact = "'; DROP TABLE users;--"
}
$idX = 0
if ($r.FinalUri -match 'id=(\d+)') { $idX = [int]$Matches[1] }
Assert ($idX -gt 0) 'Setup: malicious item created'
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '3') 'XSS: items = 3'

$r = Invoke-App "$base/item_detail.php?id=$idX" $sG
Assert ($r.Status -eq 200) 'XSS: malicious detail -> 200'
Assert (-not ($r.Content.Contains('<img src=x onerror'))) 'XSS: img payload not raw on detail'
Assert ($r.Content.Contains('&lt;img src=x onerror=alert(1)&gt;')) 'XSS: img payload escaped on detail'
Assert (-not ($r.Content.Contains('<script>alert(2)'))) 'XSS: script payload not raw on detail'
Assert ($r.Content.Contains('&lt;script&gt;alert(2)&lt;/script&gt;')) 'XSS: script payload escaped on detail'

$r = Invoke-App "$base/index.php" $sG
Assert (-not ($r.Content.Contains('<img src=x onerror'))) 'XSS: index escapes malicious title'
Assert ($r.Content.Contains('&lt;img src=x onerror=alert(1)&gt;')) 'XSS: index shows escaped title'

Assert ((Get-Sql "SELECT title FROM items WHERE id = $idX") -eq '<img src=x onerror=alert(1)>') 'Storage: title stored literally'
Assert ((Get-Sql "SELECT contact FROM items WHERE id = $idX") -eq "'; DROP TABLE users;--") 'Storage: contact stored literally'
Assert ((Get-Sql 'SELECT COUNT(*) FROM users') -eq '4') 'SQLi: DROP payload did not execute'

$r = Invoke-App "$base/item_create.php" $sB -Method POST -Body @{
    csrf_token = $tB
    title = "Admin' OR '1'='1"
    description = [string]'sql injection test'
    type = 'exchange'
    contact = '089-999-9999'
}
$idX2 = 0
if ($r.FinalUri -match 'id=(\d+)') { $idX2 = [int]$Matches[1] }
Assert ($idX2 -gt 0) 'SQLi: item with quote title created'
$r = Invoke-App "$base/index.php" $sG
Assert ($r.Status -eq 200) 'SQLi: app works with quote title'
Assert ((Get-Sql "SELECT title FROM items WHERE id = $idX2") -eq "Admin' OR '1'='1") 'SQLi: quote title stored literally'
Assert ((Get-Sql 'SELECT COUNT(*) FROM users') -eq '4') 'SQLi: users table intact'
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '4') 'SQLi: items = 4'

Write-Output '=== Positive owner actions and final state ==='

$r = Invoke-App "$base/item_complete.php?id=$idA" $sA -Method POST -Body @{ csrf_token = $tA }
Assert ((Get-Sql "SELECT status FROM items WHERE id = $idA") -eq 'completed') 'Owner completes own item'

$r = Invoke-App "$base/item_delete.php?id=$idX2" $sB -Method POST -Body @{ csrf_token = $tB }
Assert ($r.FinalUri -match 'my_items\.php') 'Owner delete own item: redirected'
Assert ((Get-Sql "SELECT COUNT(*) FROM items WHERE id = $idX2") -eq '0') 'Owner delete own item: deleted'
Assert ((Get-Sql "SELECT COUNT(*) FROM items WHERE id = $idA") -eq '1') 'Other items not affected by delete'

Assert ((Get-Sql 'SELECT COUNT(*) FROM users') -eq '4') 'Final: users = 4'
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '3') 'Final: items = 3'

Write-Output '=== AJAX email check endpoint ==='

$r = Invoke-App "$base/check_email.php?email=studenta@example.com" $sG
Assert ($r.Status -eq 200) 'AJAX: endpoint returns 200'
$json = $r.Content | ConvertFrom-Json
Assert ($json.valid -eq $true) 'AJAX: existing email marked valid'
Assert ($json.available -eq $false) 'AJAX: existing email not available'

$r = Invoke-App "$base/check_email.php?email=brand.new.user@example.com" $sG
$json = $r.Content | ConvertFrom-Json
Assert ($json.available -eq $true) 'AJAX: new email available'

$r = Invoke-App "$base/check_email.php?email=not-an-email" $sG
$json = $r.Content | ConvertFrom-Json
Assert ($json.valid -eq $false) 'AJAX: invalid format rejected'

$sqliEmail = [uri]::EscapeDataString("' OR '1'='1")
$r = Invoke-App "$base/check_email.php?email=$sqliEmail" $sG
$json = $r.Content | ConvertFrom-Json
Assert ($json.valid -eq $false) 'AJAX: SQLi-style input rejected as invalid'

Write-Output ''
Write-Output "RESULT: PASS=$script:passCount FAIL=$script:failCount"
if ($script:failCount -gt 0) { exit 1 }
exit 0
