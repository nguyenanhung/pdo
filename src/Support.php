<?php
/**
 * Project pdo
 * Created by PhpStorm
 * User: 713uk13m <dev@nguyenanhung.com>
 * Copyright: 713uk13m <dev@nguyenanhung.com>
 * Date: 10/06/2022
 * Time: 22:28
 */

namespace nguyenanhung\PDO;

use FaaPz\PDO\Clause\Conditional;

/**
 * Trait Support
 *
 * @package   nguyenanhung\PDO
 * @author    713uk13m <dev@nguyenanhung.com>
 * @copyright 713uk13m <dev@nguyenanhung.com>
 */
trait Support
{
    /**
     * Function errorException
     *
     * @param $e
     * @param $exitCode
     *
     * @return mixed
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 18/06/2022 14:00
     */
    protected function errorException($e, $exitCode)
    {
        $this->logger->error(__FUNCTION__, 'Error Message: ' . $e->getMessage());
        $this->logger->error(__FUNCTION__, 'Error Trace As String: ' . $e->getTraceAsString());

        return $exitCode;
    }

    protected function prepareWheresStatementWithField($db, $wheres, $fields = null)
    {
        if (!empty($wheres)) {
            if (is_array($wheres) && count($wheres) > 0) {
                foreach ($wheres as $field => $value) {
                    if (isset($value['operator'])) {
                        if (is_array($value['value'])) {
                            $db->where(new Conditional($value['field'], self::OPERATOR_IS_IN, $value['value']));
                        } else {
                            $db->where(new Conditional($value['field'], $value['operator'], $value['value']));
                        }
                    } else {
                        if (is_array($value)) {
                            $db->where(new Conditional($field, self::OPERATOR_IS_IN, $value));
                        } else {
                            $db->where(new Conditional($field, self::OPERATOR_EQUAL_TO, $value));
                        }
                    }
                }
            } else {
                if (empty($fields)) {
                    $fields = $this->primaryKey;
                }
                $db->where(new Conditional($fields, self::OPERATOR_EQUAL_TO, $wheres));
            }
        }
    }

    protected function prepareWheresStatement($db, $wheres)
    {
        if (!empty($wheres)) {
            if (is_array($wheres) && count($wheres) > 0) {
                foreach ($wheres as $column => $column_value) {
                    if (isset($column_value['operator'])) {
                        if (is_array($column_value['value'])) {
                            $db->where(new Conditional($column_value['field'], self::OPERATOR_IS_IN, $column_value['value']));
                        } else {
                            $db->where(new Conditional($column_value['field'], $column_value['operator'], $column_value['value']));
                        }
                    } else {
                        if (is_array($column_value)) {
                            $db->where(new Conditional($column, self::OPERATOR_IS_IN, $column_value));
                        } else {
                            $db->where(new Conditional($column, self::OPERATOR_EQUAL_TO, $column_value));
                        }
                    }
                }
            }
        }

        return $db;
    }
}
