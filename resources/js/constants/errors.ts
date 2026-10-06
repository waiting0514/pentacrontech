export const ERROR_MESSAGES = {
    // 系統與權限
    "UNAUTHORIZED": "您沒有權限執行此操作",
    "UNAUTHENTICATED": "請先登入系統",
    "SERVER_ERROR": "系統發生內部錯誤，請聯繫管理員",
    "MAINTENANCE": "系統維護中，請稍後再試",
    "TOO_MANY_REQUESTS": "請求過於頻繁，請稍後再試",

    // 資料相關
    "VALIDATION_ERROR": "資料驗證失敗，請檢查輸入欄位",
    "RESOURCE_NOT_FOUND": "查無此資料",
    "DATA_INTEGRITY_VIOLATION": "資料操作違反完整性限制（如重複資料）",
    
    // 業務邏輯 (可持續擴充)
    "INSUFFICIENT_BALANCE": "餘額不足",
    "INVALID_COUPON": "無效的優惠券代碼",
    "USER_SUSPENDED": "此帳號已遭停權",

    // 預設
    "DEFAULT": "系統發生未預期的錯誤"
};
