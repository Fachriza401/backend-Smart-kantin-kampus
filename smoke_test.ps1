# Smoke test seluruh endpoint API Smart Kantin Kampus.
#
# Pakai:
#   powershell -ExecutionPolicy Bypass -File .\backend\smoke_test.ps1
#   powershell -ExecutionPolicy Bypass -File .\backend\smoke_test.ps1 -Base http://127.0.0.1:8000/api
#
# Default menguji via IP LAN supaya sekaligus membuktikan HP bisa menjangkau
# server. Aman diulang: data unik (email, orderCode) memakai suffix acak.

param(
    [string]$Base = 'http://172.20.10.2:8000/api'
)

$script:pass = 0
$script:fail = 0
$tmp = Join-Path $env:TEMP 'smoke_req.json'
$run = (Get-Random -Minimum 10000 -Maximum 99999)

function Check {
    param([string]$Label, [string]$Method, [string]$Path, $Body, [string]$Expect)

    # Spasi pada query string harus di-encode; 'Kantin Kampus' mentah membuat
    # curl gagal parse (HTTP 000) — itu bukan bug API.
    $Url = $Path -replace ' ', '%20'

    $args = @('-s', '-m', '25', '-o', (Join-Path $env:TEMP 'smoke_out.txt'), '-w', '%{http_code}',
        '-X', $Method, ($Base + $Url), '-H', 'Accept: application/json')

    if ($null -ne $Body) {
        [System.IO.File]::WriteAllText($tmp, ($Body | ConvertTo-Json -Depth 10 -Compress),
            (New-Object System.Text.UTF8Encoding($false)))
        $args += @('-H', 'Content-Type: application/json', '--data-binary', "@$tmp")
    }

    $code = (curl.exe @args) -join ''
    $body = (Get-Content (Join-Path $env:TEMP 'smoke_out.txt') -Raw -ErrorAction SilentlyContinue)
    $ok = ($Expect -split ',').Contains($code)
    $flag = if ($ok) { 'OK  ' } else { 'GAGAL' }
    if ($ok) { $script:pass++ } else { $script:fail++ }

    $preview = if ($body) { ($body -replace '\s+', ' ').Substring(0, [Math]::Min(90, $body.Length)) } else { '(kosong)' }
    "{0} {1,-6} {2,-46} HTTP {3}  {4}" -f $flag, $Method, $Path, $code, $preview
}

Write-Host "`n=== SMOKE TEST $Base ===`n" -ForegroundColor Cyan

# ---------- Bootstrap ----------
Check 'bootstrap' POST '/bootstrap' $null '200'

# ---------- Auth & Users ----------
Check 'login email' POST '/auth/login' @{ identifier = 'admin@kantin.app'; password = 'admin123' } '200'
Check 'login NIRM' POST '/auth/login' @{ identifier = 'FACHRIZA001'; password = 'fachriza123' } '200'
Check 'login salah' POST '/auth/login' @{ identifier = 'admin@kantin.app'; password = 'x' } '401'
Check 'user by email' GET '/users?email=admin@kantin.app' $null '200'
Check 'user by nirm' GET '/users?nirm=FACHRIZA001' $null '200'
Check 'user by identifier' GET '/users?identifier=FACHRIZA001' $null '200'
Check 'user by role' GET '/users?role=tenant' $null '200'
Check 'user by id' GET '/users/1' $null '200'
Check 'register' POST '/users' @{ name = 'Smoke User'; email = "smoke$run@student.ac.id"; password = ('3' * 64); role = 'mahasiswa'; nirm = "SMOKE$run"; photoPath = $null; saldo = 1000; tenantName = $null } '201'
Check 'update user' PUT '/users/2' @{ name = 'Kantin Kampus'; email = 'tenant1@kantin.app'; password = 'b4f08230cddd4c1bc52a876e12db534f8b40eedb08ba78a5501d1cdf8eb8cb33'; role = 'tenant'; nirm = $null; photoPath = $null; saldo = 0; tenantName = 'Kantin Kampus' } '200'
Check 'update password' PUT '/users/2/password' @{ password = 'b4f08230cddd4c1bc52a876e12db534f8b40eedb08ba78a5501d1cdf8eb8cb33' } '200'

# ---------- Password Reset ----------
Check 'reset request' POST '/password-reset-requests' @{ userId = 4; role = 'mahasiswa' } '201'
Check 'reset list' GET '/password-reset-requests?status=pending' $null '200'
Check 'reset resolve' PUT '/password-reset-requests/1' @{ userId = 4; password = ('a' * 64) } '200'

# ---------- Menus ----------
Check 'menus list' GET '/menus' $null '200'
Check 'menu create' POST '/menus' @{ tenantId = 2; tenantName = 'Kantin Kampus'; name = 'Menu Smoke'; price = 1000; category = 'Snacks'; description = 'x'; rating = 0; reviewCount = 0; estimasi = '1 menit'; tersedia = 1; icon = 'star'; imageUrl = 'u' } '201'
Check 'menu update' PUT '/menus/1' @{ tenantId = 2; tenantName = 'Kantin Kampus'; name = 'Nasi Goreng Spesial'; price = 25000; category = 'Makanan'; description = 'd'; rating = 0; reviewCount = 0; estimasi = '10-15 menit'; tersedia = 1; icon = 'restaurant'; imageUrl = 'u' } '200'
Check 'menu availability' PATCH '/menus/1/availability' @{ tersedia = 0 } '200'
Check 'menu recalc rating' POST '/menus/1/recalculate-rating' $null '200'
Check 'menu delete' DELETE '/menus/41' $null '200'

# ---------- Promos ----------
Check 'promos list' GET '/promos' $null '200'
Check 'promo best-active' GET '/promos/best-active' $null '200'
Check 'promo create' POST '/promos' @{ title = 'Smoke'; description = 'x'; discount = 5; active = 1; icon = 's'; imageUrl = 'u'; startDate = $null; endDate = $null } '201'
Check 'promo update' PUT '/promos/1' @{ title = 'Diskon Spesial Hari Ini'; description = 'Diskon sampai 30% untuk menu pilihan.'; discount = 30; active = 1; icon = 'local_offer'; imageUrl = 'u'; startDate = $null; endDate = $null } '200'
Check 'promo delete' DELETE '/promos/4' $null '200'
Check 'promo sync notif' POST '/promos/sync-notifications' @{ userId = 4 } '200'

# ---------- Orders ----------
$order = @{
    userId = 4; orderCode = "SMOKE-$run"; tenantName = 'Kantin Kampus'; total = 30000
    paymentMethod = 'Bayar Langsung'; paymentRecipient = ''; paymentStatus = 'diabaikan-server'
    status = 'Menunggu Pembayaran'; pickupTime = '12:00'; note = ''
    createdAt = (Get-Date).ToString('yyyy-MM-ddTHH:mm:ss.000')
    guestName = 'Guest Smoke'; guestEmail = 'GUEST@Smoke.AC.ID'; phoneNumber = '0800'
    queueNumber = 'A99'; paymentLink = $null; paymentQrPayload = $null
    items = @(@{ menuName = 'Nasi Goreng Spesial'; price = 25000; quantity = 1 },
        @{ menuName = 'Es Teh Manis'; price = 5000; quantity = 1 })
}
Check 'order create' POST '/orders' $order '201'
Check 'order dup code' POST '/orders' $order '422'
Check 'orders by user' GET '/orders?user_id=4' $null '200'
Check 'orders by guest' GET '/orders?guest_email=guest@smoke.ac.id' $null '200'
Check 'orders by tenant' GET '/orders?tenant_name=Kantin Kampus' $null '200'
Check 'order by code' GET "/orders?order_code=SMOKE-$run" $null '200'
Check 'order by id' GET '/orders/1' $null '200'
Check 'order status' PUT '/orders/1/status' @{ status = 'Dimasak' } '200'
Check 'order confirm cash' POST '/orders/1/confirm-cash-payment' $null '200'
Check 'order virtual paid' POST '/orders/1/mark-virtual-paid' $null '200'

# ---------- Notifications ----------
Check 'notif list' GET '/notifications?user_id=4' $null '200'
Check 'notif create' POST '/notifications' @{ userId = 4; title = 'T'; message = 'M'; type = 'order'; isRead = 0; targetType = 'order'; targetId = 1 } '201'
Check 'notif unread' GET '/notifications/unread-count?user_id=4' $null '200'
Check 'notif read all' PUT '/notifications/read-all?user_id=4' $null '200'
Check 'notif delete one' DELETE '/notifications/1' $null '200'
Check 'notif delete all' DELETE '/notifications?user_id=4' $null '200'

# ---------- Topup / Rating / Favorites / Sync ----------
Check 'topup' POST '/topups' @{ userId = 4; amount = 10000; status = 'success' } '201'
Check 'rating' POST '/ratings' @{ userId = 4; menuId = 1; orderId = 1; rating = 5; review = 'bagus' } '201'
Check 'rating replace' POST '/ratings' @{ userId = 4; menuId = 1; orderId = 1; rating = 3; review = 'biasa' } '201'
Check 'favorites list' GET '/favorites?user_id=4' $null '200'
Check 'favorite add' POST '/favorites' @{ userId = 4; menuId = 1 } '201'
Check 'favorite remove' DELETE '/favorites?user_id=4&menu_id=1' $null '200'
Check 'customer sync' POST '/customers/4/sync-notifications' $null '200'

# ---------- Statistik ----------
Check 'stat users' GET '/statistics/users' $null '200'
Check 'stat ratings' GET '/statistics/ratings-summary' $null '200'
Check 'stat daily' GET '/statistics/daily-sales?days=7' $null '200'
Check 'stat monthly' GET '/statistics/monthly-sales?months=6' $null '200'
Check 'stat category' GET '/statistics/menu-category-sales' $null '200'

Write-Host "`n=== LULUS: $script:pass  GAGAL: $script:fail ===`n" -ForegroundColor $(if ($script:fail -eq 0) { 'Green' } else { 'Red' })
Remove-Item $tmp, (Join-Path $env:TEMP 'smoke_out.txt') -Force -ErrorAction SilentlyContinue
exit $script:fail
