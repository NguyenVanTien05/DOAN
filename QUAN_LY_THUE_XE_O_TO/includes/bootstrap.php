<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/connect.php";
/** @var mysqli $conn */

date_default_timezone_set("Asia/Ho_Chi_Minh");

/**
 * @param mixed $value
 * @return string
 */
function esc($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

/**
 * @param mixed $amount
 * @return string
 */
function format_money($amount)
{
    return number_format((float) $amount, 0, ",", ".") . "đ";
}

/**
 * @param mixed $plannedReturn
 * @param mixed $actualReturn
 * @param mixed $status
 * @return int
 */
function late_return_days($plannedReturn, $actualReturn = null, $status = "")
{
    if ($plannedReturn === "" || $plannedReturn === null || $status === "da_huy") {
        return 0;
    }

    $plannedDate = date("Y-m-d", strtotime((string) $plannedReturn));
    $actualDate = $actualReturn ? date("Y-m-d", strtotime((string) $actualReturn)) : date("Y-m-d");
    $plannedTimestamp = strtotime($plannedDate);
    $actualTimestamp = strtotime($actualDate);

    if (!$plannedTimestamp || !$actualTimestamp || $actualTimestamp <= $plannedTimestamp) {
        return 0;
    }

    return (int) ceil(($actualTimestamp - $plannedTimestamp) / 86400);
}

/**
 * @param mixed $plannedReturn
 * @param mixed $actualReturn
 * @param mixed $dailyPrice
 * @param mixed $status
 * @return float
 */
function calculate_late_fee($plannedReturn, $actualReturn, $dailyPrice, $status = "")
{
    $lateDays = late_return_days($plannedReturn, $actualReturn, $status);

    if ($lateDays <= 0 || $status === "cho_xac_nhan" || $status === "da_huy") {
        return 0;
    }

    return $lateDays * ((float) $dailyPrice * 0.3);
}

/**
 * @param mixed $startDate
 * @param mixed $endDate
 * @return int
 */
function booking_days_count($startDate, $endDate)
{
    $startTimestamp = strtotime(date("Y-m-d", strtotime((string) $startDate)));
    $endTimestamp = strtotime(date("Y-m-d", strtotime((string) $endDate)));

    if (!$startTimestamp || !$endTimestamp || $endTimestamp < $startTimestamp) {
        return 0;
    }

    return (int) floor(($endTimestamp - $startTimestamp) / 86400) + 1;
}
?>
