<?php
// Простой класс для работы с таблицей
class HrTimemanData
{
    // Добавить запись
    public static function add($reportId, $userId, $employeeName, $hours, $type = '', $comment = '')
    {
        global $DB;
        
        $fields = [
            'REPORT_ID' => (int)$reportId,
            'USER_ID' => (int)$userId,
            'EMPLOYEE_NAME' => $DB->ForSql($employeeName, 255),
            'HOURS' => (float)$hours,
            'INTERACTION_TYPE' => $DB->ForSql($type, 100),
            'COMMENT' => $DB->ForSql($comment),
            'CREATED_AT' => date('Y-m-d H:i:s')
        ];
        
        return $DB->Insert('b_hr_timeman_data', $fields);
    }
    
    // Получить данные по отчету
    public static function getByReport($reportId)
    {
        global $DB;
        
        $sql = "SELECT * FROM b_hr_timeman_data WHERE REPORT_ID = " . (int)$reportId . " ORDER BY ID";
        $result = $DB->Query($sql);
        
        $data = [];
        while ($row = $result->Fetch()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    // Удалить данные по отчету
    public static function deleteByReport($reportId)
    {
        global $DB;
        
        $sql = "DELETE FROM b_hr_timeman_data WHERE REPORT_ID = " . (int)$reportId;
        return $DB->Query($sql);
    }
}
?>