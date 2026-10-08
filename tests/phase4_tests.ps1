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
        $Body = $null,
        [switch]$NoRedirect
    )
    $params = @{
        Uri = $Uri
        Method = $Method
        UseBasicParsing = $true
        ErrorAction = 'Stop'
    }
    if ($Session -ne $null) { $params.WebSession = $Session }
    if ($Body -ne $null) { $params.Body = $Body }
    if ($NoRedirect) { $params.MaximumRedirection = 0 }

    try {
        $response = Invoke-WebRequest @params
        return @{
            Status = [int]$response.StatusCode
            Content = [string]$response.Content
            FinalUri = $response.BaseResponse.ResponseUri.AbsoluteUri
            Location = ''
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
            $location = ''
            try { $location = $httpResponse.Headers['Location'] } catch { }
            return @{
                Status = [int]$httpResponse.StatusCode
                Content = $content
                FinalUri = ''
                Location = $location
            }
        }
        return @{ Status = 0; Content = $_.Exception.Message; FinalUri = ''; Location = '' }
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

$sA = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$sB = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$sG = New-Object Microsoft.PowerShell.Commands.WebRequestSession

Write-Output '=== Setup: reset test data ==='

& $mysql -u root msu_share_care -e 'DELETE FROM items; DELETE FROM users; ALTER TABLE users AUTO_INCREMENT = 1; ALTER TABLE items AUTO_INCREMENT = 1;'
Assert ((Get-Sql 'SELECT COUNT(*) FROM users') -eq '0') 'Setup: users table reset'

$r = Invoke-App "$base/register.php" $sA
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/register.php" $sA -Method POST -Body @{
    csrf_token = $t
    full_name = 'Test Student A'
    email = 'studenta@example.com'
    password = 'TestPass123'
    password_confirm = 'TestPass123'
    contact_info = '081-234-5678'
}
Assert ($r.Content -match 'nav-user') 'Setup: register + auto-login user A'
Assert ((Get-Sql "SELECT id FROM users WHERE email = 'studenta@example.com'") -eq '1') 'Setup: A has id 1'

$r = Invoke-App "$base/logout.php" $sA -Method POST -Body @{ csrf_token = (Get-CsrfToken $r.Content) }
Assert ($r.Content -match 'login\.php') 'Setup: logout A works'

$r = Invoke-App "$base/login.php" $sA
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/login.php" $sA -Method POST -Body @{
    csrf_token = $t
    email = 'studenta@example.com'
    password = 'TestPass123'
}
Assert ($r.Content -match 'Test Student A') 'Setup: login user A'

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
Assert ($r.Content -match 'nav-user') 'Setup: register + auto-login user B'
Assert ((Get-Sql "SELECT id FROM users WHERE email = 'studentb@example.com'") -eq '2') 'Setup: B has id 2'

Write-Output '=== Create validation ==='

$r = Invoke-App "$base/item_create.php" $sA
Assert ($r.Status -eq 200) 'GET item_create form returns 200'
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/item_create.php" $sA -Method POST -Body @{
    csrf_token = $t
    title = ''
    description = ''
    type = 'donate'
    contact = ''
}
Assert ($r.Content.Contains('กรุณากรอกชื่อสิ่งของ')) 'Create: server-side validation for empty title'
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '0') 'Create: invalid input inserts nothing'

Write-Output '=== Case A: owner creates / edits ==='

$r = Invoke-App "$base/item_create.php" $sA
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/item_create.php" $sA -Method POST -Body @{
    csrf_token = $t
    title = 'กระเป๋าเป้ เก่าใช้แล้ว'
    description = 'กระเป๋าเป้สภาพดี ไม่ได้ใช้แล้ว ต้องการแบ่งปัน'
    type = 'donate'
    contact = '081-234-5678'
}
$sourceUri = $r.FinalUri
$hasId = $sourceUri -match 'id=(\d+)'
$itemId = 0
if ($hasId) { $itemId = [int]$Matches[1] }
Assert $hasId 'Create: redirects to item detail'
Assert ($itemId -gt 0) "Create: item id parsed ($itemId)"
Assert ($r.Content.Contains('กระเป๋าเป้ เก่าใช้แล้ว')) 'Create: detail shows new item title'
Assert ((Get-Sql "SELECT owner_id FROM items WHERE id=$itemId") -eq '1') 'Create: owner_id = A (id 1)'
Assert ((Get-Sql "SELECT status FROM items WHERE id=$itemId") -eq 'available') 'Create: status = available'

$r = Invoke-App "$base/item_edit.php?id=$itemId" $sA
Assert ($r.Status -eq 200) 'Edit: owner GET edit form 200'
Assert ($r.Content.Contains('กระเป๋าเป้ เก่าใช้แล้ว')) 'Edit: form prefilled with current title'
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/item_edit.php?id=$itemId" $sA -Method POST -Body @{
    csrf_token = $t
    title = 'กระเป๋าเป้ แก้ไขแล้ว'
    description = 'รายละเอียดใหม่หลังแก้ไข'
    type = 'exchange'
    contact = '081-234-5678'
}
Assert ($r.Content.Contains('กระเป๋าเป้ แก้ไขแล้ว')) 'Edit: redirects to detail with updated title'
Assert ((Get-Sql "SELECT type FROM items WHERE id=$itemId") -eq 'exchange') 'Edit: DB type updated to exchange'
Assert ((Get-Sql "SELECT title FROM items WHERE id=$itemId").Contains('แก้ไขแล้ว')) 'Edit: DB title updated'

$r = Invoke-App "$base/my_items.php" $sA
Assert ($r.Status -eq 200 -and $r.Content.Contains('กระเป๋าเป้ แก้ไขแล้ว')) 'My items: shows A own item'

$r = Invoke-App "$base/index.php" $sA
Assert ($r.Content.Contains('กระเป๋าเป้ แก้ไขแล้ว')) 'Index: lists the item'

Write-Output '=== Case B: other user views A item ==='

$r = Invoke-App "$base/item_detail.php?id=$itemId" $sB
Assert ($r.Status -eq 200) 'B can view A item detail'
Assert (-not ($r.Content -match 'item_edit\.php\?id=')) 'B sees no edit button'
Assert (-not ($r.Content -match 'item_delete\.php\?id=')) 'B sees no delete button'
Assert ($r.Content.Contains('081-234-5678')) 'Detail shows owner contact'

$r = Invoke-App "$base/item_detail.php?id=$itemId" $sG
Assert ($r.Status -eq 200) 'Guest can view detail'
Assert (-not ($r.Content -match 'item_edit\.php\?id=')) 'Guest sees no edit button'

$r = Invoke-App "$base/item_edit.php?id=$itemId" $sB
Assert ($r.Status -eq 403) 'B GET A edit form -> 403'

Write-Output '=== Case C: ID manipulation by B ==='

$r = Invoke-App "$base/item_create.php" $sB
$tB = Get-CsrfToken $r.Content

$r = Invoke-App "$base/item_delete.php?id=$itemId" $sB -Method POST -Body @{ csrf_token = $tB }
Assert ($r.Status -eq 403) 'B POST delete A item id -> 403'
Assert ((Get-Sql "SELECT COUNT(*) FROM items WHERE id=$itemId") -eq '1') 'Item survives B delete attempt'

$r = Invoke-App "$base/item_complete.php?id=$itemId" $sB -Method POST -Body @{ csrf_token = $tB }
Assert ($r.Status -eq 403) 'B POST complete A item id -> 403'
Assert ((Get-Sql "SELECT status FROM items WHERE id=$itemId") -eq 'available') 'Status unchanged after B attempt'

$r = Invoke-App "$base/item_edit.php?id=$itemId" $sB -Method POST -Body @{
    csrf_token = $tB
    title = 'HACKED BY B'
    description = 'x'
    type = 'donate'
    contact = 'x'
}
Assert ($r.Status -eq 403) 'B POST edit A item id -> 403'
Assert (-not ((Get-Sql "SELECT title FROM items WHERE id=$itemId").Contains('HACKED'))) 'Title not changed by B'

$r = Invoke-App "$base/item_delete.php?id=$itemId" $sB -Method POST -Body @{}
Assert ($r.FinalUri -match 'my_items\.php') 'B POST delete without CSRF -> redirected away, no state change'
Assert ((Get-Sql "SELECT COUNT(*) FROM items WHERE id=$itemId") -eq '1') 'Item survives no-CSRF delete attempt'

Write-Output '=== Guest must log in ==='

$r = Invoke-App "$base/item_create.php" $sG -Method POST -Body @{ title = 'x' }
Assert ($r.FinalUri -match 'login\.php') 'Guest POST item_create -> redirected to login'

$r = Invoke-App "$base/item_edit.php?id=$itemId" $sG
Assert ($r.FinalUri -match 'login\.php') 'Guest GET edit -> redirected to login'

$r = Invoke-App "$base/my_items.php" $sG
Assert ($r.FinalUri -match 'login\.php') 'Guest GET my_items -> redirected to login'

Write-Output '=== Owner marks Completed ==='

$r = Invoke-App "$base/item_detail.php?id=$itemId" $sA
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/item_complete.php?id=$itemId" $sA -Method POST -Body @{ csrf_token = $t }
Assert ((Get-Sql "SELECT status FROM items WHERE id=$itemId") -eq 'completed') 'A marks own item completed'
Assert ($r.Content -match 'badge-completed') 'Detail shows Completed badge'
Assert (-not ($r.Content -match 'item_complete\.php')) 'Complete button hidden when completed'

Write-Output '=== XSS: item title escaped ==='

$r = Invoke-App "$base/item_create.php" $sA
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/item_create.php" $sA -Method POST -Body @{
    csrf_token = $t
    title = '<img src=x onerror=alert(1)>'
    description = 'xss test item'
    type = 'donate'
    contact = '081-234-5678'
}
$id2 = 0
if ($r.FinalUri -match 'id=(\d+)') { $id2 = [int]$Matches[1] }
Assert ($id2 -gt 0) "XSS: second item created (id $id2)"

$r = Invoke-App "$base/index.php" $sG
Assert (-not ($r.Content.Contains('<img src=x onerror=alert(1)>'))) 'XSS: raw tag not in index output'
Assert ($r.Content.Contains('&lt;img src=x onerror=alert(1)&gt;')) 'XSS: title escaped in index output'

$r = Invoke-App "$base/item_detail.php?id=$id2" $sG
Assert (-not ($r.Content.Contains('<img src=x onerror=alert(1)>'))) 'XSS: raw tag not in detail output'

Write-Output '=== Owner deletes (Case A) ==='

$r = Invoke-App "$base/item_detail.php?id=$id2" $sA
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/item_delete.php?id=$id2" $sA -Method POST -Body @{ csrf_token = $t }
Assert ($r.FinalUri -match 'my_items\.php') 'A deletes own item2 -> redirected to my_items'
Assert ((Get-Sql "SELECT COUNT(*) FROM items WHERE id=$id2") -eq '0') 'Item2 removed from DB'

$r = Invoke-App "$base/item_detail.php?id=$itemId" $sA
$t = Get-CsrfToken $r.Content
$r = Invoke-App "$base/item_delete.php?id=$itemId" $sA -Method POST -Body @{ csrf_token = $t }
Assert ($r.FinalUri -match 'my_items\.php') 'A deletes own item1 -> redirected to my_items'
Assert ((Get-Sql 'SELECT COUNT(*) FROM items') -eq '0') 'All items removed from DB'

$r = Invoke-App "$base/index.php" $sG
Assert ($r.Content -match 'empty-state') 'Index shows empty state again'

Write-Output '=== Error cases: bad ids ==='

$r = Invoke-App "$base/item_edit.php?id=99999" $sA
Assert ($r.Status -eq 404) 'Edit nonexistent id -> 404'
$r = Invoke-App "$base/item_detail.php?id=99999" $sG
Assert ($r.Status -eq 404) 'Detail nonexistent id -> 404'
$r = Invoke-App "$base/item_detail.php?id=abc" $sG
Assert ($r.Status -eq 404) 'Detail invalid id -> 404'
$r = Invoke-App "$base/item_edit.php?id=0" $sA
Assert ($r.Status -eq 404) 'Edit id=0 -> 404'

Write-Output ''
Write-Output "RESULT: PASS=$script:passCount FAIL=$script:failCount"
if ($script:failCount -gt 0) { exit 1 }
exit 0
