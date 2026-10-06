# API 測試腳本

Write-Host "=== AI Agency Backend API 測試 ===" -ForegroundColor Cyan
Write-Host ""

# 測試 1: 檢查 API 是否運行
Write-Host "1. 測試 API 連接..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "http://localhost/api/test" -Method Get
    Write-Host "✓ API 正常運行: $($response.message)" -ForegroundColor Green
} catch {
    Write-Host "✗ API 連接失敗" -ForegroundColor Red
    Write-Host $_.Exception.Message
    exit 1
}

Write-Host ""

# 測試 2: 建立測試使用者並取得 Token
Write-Host "2. 建立測試使用者..." -ForegroundColor Yellow
Write-Host "請在另一個終端執行以下命令:" -ForegroundColor Cyan
Write-Host ""
Write-Host "./vendor/bin/sail artisan tinker" -ForegroundColor White
Write-Host ""
Write-Host "然後執行:" -ForegroundColor Cyan
Write-Host @"
`$user = \App\Models\User::firstOrCreate(
    ['email' => 'test@example.com'],
    [
        'name' => 'Test User',
        'password' => bcrypt('password'),
        'monthly_budget_usd' => 50.00
    ]
);
`$token = `$user->createToken('test-token')->plainTextToken;
echo `$token;
"@ -ForegroundColor White

Write-Host ""
Write-Host "複製輸出的 Token,然後按 Enter 繼續..."
$token = Read-Host "請貼上 Token"

if ([string]::IsNullOrWhiteSpace($token)) {
    Write-Host "✗ 未提供 Token" -ForegroundColor Red
    exit 1
}

Write-Host ""

# 測試 3: 測試認證 API
Write-Host "3. 測試認證 API..." -ForegroundColor Yellow
try {
    $headers = @{
        "Authorization" = "Bearer $token"
        "Accept" = "application/json"
    }
    $user = Invoke-RestMethod -Uri "http://localhost/api/user" -Method Get -Headers $headers
    Write-Host "✓ 認證成功! 使用者: $($user.name)" -ForegroundColor Green
} catch {
    Write-Host "✗ 認證失敗" -ForegroundColor Red
    Write-Host $_.Exception.Message
    exit 1
}

Write-Host ""

# 測試 4: 測試產品 API
Write-Host "4. 測試產品 API..." -ForegroundColor Yellow
try {
    $products = Invoke-RestMethod -Uri "http://localhost/api/products" -Method Get -Headers $headers
    Write-Host "✓ 產品 API 正常! 產品數量: $($products.data.Count)" -ForegroundColor Green
} catch {
    Write-Host "✗ 產品 API 失敗" -ForegroundColor Red
    Write-Host $_.Exception.Message
}

Write-Host ""

# 顯示前端設定指令
Write-Host "=== 前端設定 ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "在瀏覽器 Console (F12) 執行:" -ForegroundColor Yellow
Write-Host "localStorage.setItem('api_token', '$token');" -ForegroundColor White
Write-Host ""
Write-Host "然後訪問: http://localhost/dashboard" -ForegroundColor Green
Write-Host ""
