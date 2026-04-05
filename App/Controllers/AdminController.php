<?php

namespace App\Controllers;

use App\Services\Database\SQL;
use App\Services\Logging\LogStatus;
use App\Models\Config\Log;
use App\Services\Router\DataValidator;
use App\Services\Router\Request;
use App\Services\Router\RequestError;
use App\Services\Router\Response;
use DateTime;
use Exception;

class AdminController
{
    public const MAX_LOG_COUNT_PER_PAGE = 200;

    /**
     * @param Log $log
     * @return array{category: array, description: string, id: int, name: string, tags: array}
     */
    private static function CreateLogData(Log $log): array
    {
        return [
            'id' => $log->id,
            'status' => $log->status,
            'origin' => $log->origin,
            'message' => $log->message,
            'date' => $log->date,
        ];
    }

    /**
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function SelectAllLogs(): \Closure
    {
        return function (Request $req, Response $res): void {
            $data = $req->GetJSON();

            DataValidator::ValidateFieldsAre([DataValidator::REQUIRED, DataValidator::NUMERIC], $data, ['page', 'count']);

            $page = intval($data['page']);
            $countPerPage = intval($data['count']);

            if ($countPerPage <= 0 || $countPerPage > self::MAX_LOG_COUNT_PER_PAGE) {
                throw RequestError::CreateFieldError(416, 'count', '%key% must be in range (1-' . self::MAX_LOG_COUNT_PER_PAGE . ')');
            }

            $assetCount = SQL::SelectTableCount('*', Log::GetTableName());
            $pageCount = max(ceil($assetCount / $countPerPage), 1);

            if ($page < 0 || $page >= $pageCount) {
                throw RequestError::CreateFieldError(416, 'page', '%key% must be in range (0-' . ($pageCount - 1) . ')', ['totalPages' => $pageCount]);
            }

            $logModels = Log::SelectAllModelsLimited($countPerPage, $countPerPage * $page);

            $logs = array_map(function (Log $log) {
                return self::CreateLogData($log);
            }, $logModels);

            $res->SetJSON([
                'logs' => $logs,
                'info' => [
                    'page' => $page,
                    'count' => $countPerPage,
                    'pageCount' => $pageCount
                ]
            ]);
        };
    }

    /**
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function SearchLogs(): \Closure
    {
        return function (Request $req, Response $res): void {
            $data = $req->GetJSON();

            DataValidator::ValidateFieldsAre([DataValidator::REQUIRED, DataValidator::NUMERIC], $data, ['page', 'count']);

            $page = intval($data['page']);
            $countPerPage = intval($data['count']);

            if ($countPerPage <= 0 || $countPerPage > self::MAX_LOG_COUNT_PER_PAGE) {
                throw RequestError::CreateFieldError(416, 'count', '%key% must be in range (1-' . self::MAX_LOG_COUNT_PER_PAGE . ')');
            }

            $statusQuery = $data['statusQuery'] ?? [];
            $dateStartQuery = $data['dateStartQuery'] ?? '';
            $dateEndQuery = $data['dateEndQuery'] ?? '';

            if (!is_array($statusQuery)) {
                throw RequestError::CreateFieldError(400, 'statusQuery', '%key% must be an array of strings');
            }

            foreach ($statusQuery as $status) {
                if (!LogStatus::IsStatus($status)) {
                    throw RequestError::CreateFieldError(400, 'statusQuery', 'Each %key% must be a valid status (' . join(', ', LogStatus::ALL_STATUSES) . ')');
                }
            }

            $searchConditions = [];
            $searchParams = [];
            $tableName = Log::GetTableName();

            if (count($statusQuery) > 0) {
                $placeholders = [];
                foreach ($statusQuery as $i => $status) {
                    $key = ":status$i";
                    $placeholders[] = $key;
                    $searchParams[$key] = $status;
                }
                $searchConditions[] = "status IN (" . implode(',', $placeholders) . ")";
            }

            try {
                if (!empty($dateStartQuery)) {
                    $searchConditions[] = "date >= :dateStart";
                    $searchParams[':dateStart'] = (new DateTime($dateStartQuery))->format('Y-m-d H:i:s');
                }

                if (!empty($dateEndQuery)) {
                    $searchConditions[] = "date <= :dateEnd";
                    $searchParams[':dateEnd'] = (new DateTime($dateEndQuery))->format('Y-m-d H:i:s');
                }
            } catch (Exception $e) {
                throw RequestError::CreateFieldError(400, 'dateQuery', 'Invalid date format provided');
            }

            if (count($searchConditions) === 0) {
                throw RequestError::CreateFieldError(400, 'query', 'Not a single filter specified');
            }

            $searchSql = join(' AND ', $searchConditions);
            $countSql = "SELECT COUNT(*) FROM $tableName WHERE $searchSql";
            $countStmt = SQL::MiscExecute($countSql, $searchParams);
            $totalResults = intval($countStmt->fetchColumn());

            $pageCount = max(1, ceil($totalResults / $countPerPage));

            if ($page < 0 || ($totalResults > 0 && $page >= $pageCount)) {
                throw RequestError::CreateFieldError(416, 'page', '%key% must be in range (0-' . ($pageCount - 1) . ')', ['totalPages' => $pageCount]);
            }

            $limit = intval($countPerPage);
            $offset = intval($countPerPage * $page);

            $sql = "SELECT * FROM $tableName WHERE $searchSql ORDER BY date DESC LIMIT $limit OFFSET $offset";
            $sqlCom = SQL::MiscExecute($sql, $searchParams);

            $logModels = array_map(function ($row) {
                return Log::CreateFrom($row);
            }, $sqlCom->fetchAll(\PDO::FETCH_ASSOC));

            $logs = array_map(function (Log $log) {
                return self::CreateLogData($log);
            }, $logModels);

            $res->SetJSON([
                'logs' => $logs,
                'info' => [
                    'page' => $page,
                    'count' => $countPerPage,
                    'pageCount' => $pageCount,
                    'totalResults' => $totalResults
                ]
            ]);
        };
    }
}
