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
use FaaPz\PDO\Clause\Limit;

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

        return $db;
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

    protected function prepareOptionsStatement($db, $options)
    {
        if (isset($options['limit'], $options['offset']) && $options['limit'] > 0) {
            $page = $this->preparePaging($options['offset'], $options['limit']);
            $db->limit(new Limit($page['limit'], $page['offset']));
        }
        if (isset($options['orderBy']) && is_array($options['orderBy'])) {
            foreach ($options['orderBy'] as $column => $direction) {
                $db->orderBy($column, $direction);
            }
        }

        return $db;
    }

    public function filterRecordIsActive($db, string $field = 'status')
    {
        $db->where(new Conditional($this->table . '.' . $field, self::OPERATOR_EQUAL_TO, self::TABLE_OPERATOR_IS_ACTIVE));

        return $db;
    }

    public function filterByPrimaryId($db, $id, string $field = 'id')
    {
        if ($id !== null) {
            if (is_array($id)) {
                $db->where(new Conditional($this->table . '.' . $field, self::OPERATOR_IS_IN, $id));
            } else {
                $db->where(new Conditional($this->table . '.' . $field, self::OPERATOR_EQUAL_TO, $id));
            }
        }

        return $db;
    }

    public function bindRecursiveFromCategory($db, $recursive, $parentId, string $field = 'categoryId')
    {
        if (is_array($recursive) || is_object($recursive)) {
            /**
             * Xác định lấy toàn bộ tin tức ở các category con
             */
            $countSubCategory = count($recursive); // Đếm bảng ghi Category con
            if ($countSubCategory) {
                // Nếu tồn tại các category con
                $listCategory = array();
                $listCategory[] = $parentId; // Push category cha
                foreach ($recursive as $item) {
                    $itemId = is_array($item) ? $item['id'] : $item->id;
                    $listCategory[] = (int) $itemId; // Push các category con vào mảng dữ liệu
                }
                $db->where(new Conditional($this->table . '.' . $field, self::OPERATOR_IS_IN, $listCategory)); // Lấy theo where in
            } else {
                $db->where(new Conditional($this->table . '.' . $field, self::OPERATOR_EQUAL_TO, $parentId)); // lấy theo where
            }
        } else {
            // Trong trường hợp so sánh tuyệt đối đối với categoryId truyền vào
            $db->where(new Conditional($this->table . '.' . $field, self::OPERATOR_EQUAL_TO, $parentId));
        }

        return $db;
    }
}
