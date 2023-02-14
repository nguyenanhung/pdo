<?php
/**
 * Project pdo.
 * Created by PhpStorm.
 * User: 713uk13m <dev@nguyenanhung.com>
 * Date: 2021-08-28
 * Time: 10:21
 */

namespace nguyenanhung\PDO;

use PDO;
use FaaPz\PDO\Database;
use FaaPz\PDO\Clause\Conditional;
use FaaPz\PDO\Clause\Limit;

/**
 * Class MySQLPDOBaseModel
 *
 * @package   nguyenanhung\PDO
 * @author    713uk13m <dev@nguyenanhung.com>
 * @copyright 713uk13m <dev@nguyenanhung.com>
 */
class MySQLPDOBaseModel
{
    const VERSION = '2.0.7';
    const LAST_MODIFIED = '2023-01-14';
    const AUTHOR_NAME = 'Hung Nguyen';
    const AUTHOR_EMAIL = 'dev@nguyenanhung.com';
    const PROJECT_NAME = 'Database Wrapper - PDO Database Model';
    const OPERATOR_EQUAL_TO = '=';
    const OP_EQ = '=';
    const OPERATOR_NOT_EQUAL_TO = '!=';
    const OP_NE = '!=';
    const OPERATOR_LESS_THAN = '<';
    const OP_LT = '<';
    const OPERATOR_LESS_THAN_OR_EQUAL_TO = '<=';
    const OP_LTE = '<=';
    const OPERATOR_GREATER_THAN = '>';
    const OP_GT = '>';
    const OPERATOR_GREATER_THAN_OR_EQUAL_TO = '>=';
    const OP_GTE = '>=';
    const OPERATOR_IS_SPACESHIP = '<=>';
    const OPERATOR_IS_IN = 'IN';
    const OPERATOR_IS_LIKE = 'LIKE';
    const OPERATOR_IS_LIKE_BINARY = 'LIKE BINARY';
    const OPERATOR_IS_ILIKE = 'ilike';
    const OPERATOR_IS_NOT_LIKE = 'NOT LIKE';
    const OPERATOR_IS_NULL = 'IS NULL';
    const OPERATOR_IS_NOT_NULL = 'IS NOT NULL';
    const ORDER_ASCENDING = 'ASC';
    const ORDER_DESCENDING = 'DESC';

    /** @var \nguyenanhung\MyDebug\Logger Đối tượng khởi tạo dùng gọi đến Class Debug */
    protected $logger;

    /** @var \nguyenanhung\MyDebug\Logger Đối tượng khởi tạo dùng gọi đến Class Debug */
    protected $debug;

    /** @var array|null Mảng dữ liệu chứa thông tin database cần kết nối tới */
    protected $database;

    /** @var string DB Name */
    protected $dbName = 'default';

    /** @var string|null Bảng cần lấy dữ liệu */
    protected $table;

    /** @var Database $db */
    protected $db;

    /** @var bool Cấu hình trạng thái Debug, TRUE nếu bật, FALSE nếu tắt */
    public $debugStatus = false;

    /** @var null|string Cấu hình Level Debug */
    public $debugLevel = 'error';

    /** @var null|bool|string Cấu hình thư mục lưu trữ Log, VD: /your/to/path */
    public $debugLoggerPath = '';

    /** @var null|string Cấu hình File Log, VD: Log-2018-10-15.log | Log-date('Y-m-d').log */
    public $debugLoggerFilename = '';

    /** @var string Primary Key Default */
    public $primaryKey = 'id';

    /**
     * MySQLPDOBaseModel constructor.
     *
     * @param array $database
     *
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     */
    public function __construct(array $database = array())
    {
        if (class_exists('nguyenanhung\MyDebug\Logger')) {
            $this->logger = new \nguyenanhung\MyDebug\Logger();
            if ($this->debugStatus === true) {
                $this->logger->setDebugStatus($this->debugStatus);
                if ($this->debugLevel) {
                    $this->logger->setGlobalLoggerLevel($this->debugLevel);
                }
                if ($this->debugLoggerPath) {
                    $this->logger->setLoggerPath($this->debugLoggerPath);
                }
                if (empty($this->debugLoggerFilename)) {
                    $this->debugLoggerFilename = 'Log-' . date('Y-m-d') . '.log';
                }
                $this->logger->setLoggerSubPath(__CLASS__);
                $this->logger->setLoggerFilename($this->debugLoggerFilename);
            }
        }
        $this->debug = $this->logger;
        if (!empty($database)) {
            $this->database = $database;
        }
        $this->setupDatabaseConnection();
    }

    /**
     * PDOBaseModel destructor.
     */
    public function __destruct()
    {
    }

    /**
     * Function setupDatabaseConnection
     *
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 09/16/2021 53:08
     */
    protected function setupDatabaseConnection()
    {
        if (is_array($this->database) && !empty($this->database)) {
            $this->db = new Database(
                $this->database['driver'] . ':host=' . $this->database['host'] . ';port=' . $this->database['port'] . ';dbname=' . $this->database['database'] . ';charset=' . $this->database['charset'] . ';collation=' . $this->database['collation'] . ';prefix=' . $this->database['prefix'],
                $this->database['username'],
                $this->database['password'],
                $this->database['options']
            );
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        }
    }

    /**
     * Function getVersion
     *
     * @author: 713uk13m <dev@nguyenanhung.com>
     * @time  : 9/28/18 14:47
     *
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Function setPrimaryKey
     *
     * @param $primaryKey
     *
     * @return $this
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 08/28/2021 34:28
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * Function getPrimaryKey
     *
     * @return string
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 08/02/2020 41:53
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Function preparePaging
     *
     * @param int $pageIndex
     * @param int $pageSize
     *
     * @return array
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 08/21/2021 23:24
     */
    public function preparePaging($pageIndex = 1, $pageSize = 10)
    {
        if ($pageIndex !== 0) {
            if ($pageIndex <= 0 || empty($pageIndex)) {
                $pageIndex = 1;
            }
            $offset = ($pageIndex - 1) * $pageSize;
        } else {
            $offset = $pageIndex;
        }

        return array('offset' => $offset, 'limit' => $pageSize);
    }

    /**
     * Function setDatabase
     *
     * @param array  $database
     * @param string $name
     *
     * @return $this
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 44:12
     */
    public function setDatabase($database = array(), $name = 'default')
    {
        $this->database = $database;
        $this->dbName = $name;

        return $this;
    }

    /**
     * Function getDatabase
     *
     * @return array|null
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 44:18
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Function setTable
     *
     * @param string $table
     *
     * @return $this
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 44:22
     */
    public function setTable($table = '')
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Function getTable
     *
     * @return string|null
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 44:26
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Function connection
     *
     * @return $this
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 44:31
     */
    public function connection()
    {
        if (!is_object($this->db)) {
            $this->setupDatabaseConnection();
        }

        return $this;
    }

    /**
     * Function disconnect
     *
     * @return $this
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 44:37
     */
    public function disconnect()
    {
        if (isset($this->db)) {
            $this->db = null;
        }

        return $this;
    }

    /**
     * Function getDb
     *
     * @return \FaaPz\PDO\Database
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 09/16/2021 55:24
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Function countAll - Hàm đếm toàn bộ bản ghi tồn tại trong bảng
     *
     * @param string|array $select
     *
     * @return int
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 45:10
     */
    public function countAll($select = array('id'))
    {
        $this->connection();

        //$this->logger->debug(__FUNCTION__, 'Total Result: ' . $total);

        return $this->db->select($select)->from($this->table)->execute()->rowCount();
    }

    /**
     * Function checkExists - Hàm kiểm tra sự tồn tại bản ghi theo tham số đầu vào
     *
     * @param string|array      $whereValue Giá trị cần kiểm tra
     * @param string|null       $whereField Field tương ứng, ví dụ: ID
     * @param string|array|null $select     Bản ghi cần chọn
     *
     * @return int Số lượng bàn ghi tồn tại phù hợp với điều kiện đưa ra
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/16/18 11:45
     */
    public function checkExists($whereValue = '', $whereField = 'id', $select = array('*'))
    {
        $this->connection();
        $db = $this->db->select($select)->from($this->table);
        if (is_array($whereValue) && count($whereValue) > 0) {
            foreach ($whereValue as $column => $column_value) {
                if (is_array($column_value)) {
                    $db->where(new Conditional($column, self::OPERATOR_IS_IN, $column_value));
                } else {
                    $db->where(new Conditional($column, self::OPERATOR_EQUAL_TO, $column_value));
                }
            }
        } else {
            $db->where(new Conditional($whereField, self::OPERATOR_EQUAL_TO, $whereValue));
        }

        //$this->logger->debug(__FUNCTION__, 'Total Result: ' . $total);

        return $db->execute()->rowCount();
    }

    /**
     * Hàm kiểm tra sự tồn tại bản ghi theo tham số đầu vào - Đa điều kiện
     *
     * @param string|array      $whereValue Giá trị cần kiểm tra
     * @param string|null       $whereField Field tương ứng, ví dụ: ID
     * @param string|array|null $select     Bản ghi cần chọn
     *
     * @return int Số lượng bàn ghi tồn tại phù hợp với điều kiện đưa ra
     * @author    : 713uk13m <dev@nguyenanhung.com>
     * @copyright : 713uk13m <dev@nguyenanhung.com>
     * @time      : 10/16/18 11:45
     */
    public function checkExistsWithMultipleWhere($whereValue = '', $whereField = 'id', $select = array('*'))
    {
        $this->connection();
        $db = $this->db->select($select)->from($this->table);
        if (!empty($whereValue)) {
            if (is_array($whereValue) && count($whereValue) > 0) {
                foreach ($whereValue as $value) {
                    if (is_array($value['value'])) {
                        $db->where(new Conditional($value['field'], self::OPERATOR_IS_IN, $value['value']));
                    } else {
                        $db->where(new Conditional($value['field'], $value['operator'], $value['value']));
                    }
                }
            } else {
                $db->where(new Conditional($whereField, self::OPERATOR_EQUAL_TO, $whereValue));
            }
        }

        return $db->execute()->rowCount();
    }

    /**
     * Function getLatest - Hàm lấy bản ghi mới nhất theo điều kiện
     *
     * Mặc định giá trị so sánh dựa trên column created_at
     *
     * @param array  $selectField Danh sách các column cần lấy
     * @param string $byColumn    Column cần so sánh dữ liệu, mặc định sẽ sử dụng column created_at
     *
     * @return mixed
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 53:20
     */
    public function getLatest(array $selectField = array('*'), $byColumn = 'created_at')
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = array($selectField);
        }
        $db = $this->db->select($selectField)->from($this->table);
        $db->orderBy($byColumn, self::ORDER_DESCENDING)->limit(new Limit(1));

        // $this->logger->debug(__FUNCTION__, 'GET Result => ' . json_encode($result));
        return $db->execute()->fetch();
    }

    /**
     * Function getLatestByColumn
     *
     * @param $wheres
     * @param $selectField
     * @param $column
     * @param $fields
     *
     * @return mixed
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/06/2022 28:55
     */
    public function getLatestByColumn($wheres = array(), $selectField = array('*'), $column = 'id', $fields = 'id')
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = array($selectField);
        }
        $db = $this->db->select($selectField)->from($this->table);
        if (!empty($wheres)) {
            if (is_array($wheres) && count($wheres) > 0) {
                foreach ($wheres as $value) {
                    if (is_array($value['value'])) {
                        $db->where(new Conditional($value['field'], self::OPERATOR_IS_IN, $value['value']));
                    } else {
                        $db->where(new Conditional($value['field'], $value['operator'], $value['value']));
                    }
                }
            } else {
                $db->where(new Conditional($fields, self::OPERATOR_EQUAL_TO, $wheres));
            }
        }
        $db->orderBy($column, self::ORDER_DESCENDING)->limit(new Limit(1));

        // $this->logger->debug(__FUNCTION__, 'GET Result => ' . json_encode($result));
        return $db->execute()->fetch();
    }


    /**
     * Function getOldest - Hàm lấy bản ghi cũ nhất nhất theo điều kiện
     *
     * Mặc định giá trị so sánh dựa trên column created_at
     *
     * @param array  $selectField Danh sách các column cần lấy
     * @param string $byColumn    Column cần so sánh dữ liệu, mặc định sẽ sử dụng column created_at
     *
     * @return mixed
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 54:15
     */
    public function getOldest(array $selectField = array('*'), $byColumn = 'created_at')
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = array($selectField);
        }
        $db = $this->db->select($selectField)->from($this->table);
        $db->orderBy($byColumn, self::ORDER_ASCENDING)->limit(new Limit(1));

        // $this->logger->debug(__FUNCTION__, 'GET Result => ' . json_encode($result));

        return $db->execute()->fetch();
    }

    /**
     * Function getOldestByColumn
     *
     * @param $wheres
     * @param $selectField
     * @param $column
     * @param $fields
     *
     * @return mixed
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/06/2022 28:49
     */
    public function getOldestByColumn($wheres = array(), $selectField = array('*'), $column = 'id', $fields = 'id')
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = array($selectField);
        }
        $db = $this->db->select($selectField)->from($this->table);
        if (!empty($wheres)) {
            if (is_array($wheres) && count($wheres) > 0) {
                foreach ($wheres as $value) {
                    if (is_array($value['value'])) {
                        $db->where(new Conditional($value['field'], self::OPERATOR_IS_IN, $value['value']));
                    } else {
                        $db->where(new Conditional($value['field'], $value['operator'], $value['value']));
                    }
                }
            } else {
                $db->where(new Conditional($fields, self::OPERATOR_EQUAL_TO, $wheres));
            }
        }
        $db->orderBy($column, self::ORDER_ASCENDING)->limit(new Limit(1));

        // $this->logger->debug(__FUNCTION__, 'GET Result => ' . json_encode($result));
        return $db->execute()->fetch();
    }

    /**
     * Hàm lấy thông tin bản ghi theo tham số đầu vào
     *
     * Đây là hàm cơ bản, chỉ áp dụng check theo 1 field
     *
     * Lấy bản ghi đầu tiên phù hợp với điều kiện
     *
     * @param array|string      $value       Giá trị cần kiểm tra
     * @param null|string       $field       Field tương ứng, ví dụ: ID
     * @param null|string       $format      Format dữ liệu đầu ra: null, json, array, base, result
     * @param null|string|array $selectField Các field cần lấy
     *
     * @return object|array|string|null Mảng|String|Object dữ liều phụ hợp với yêu cầu map theo biến format truyền vào
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/16/18 11:51
     */
    public function getInfo($value = '', $field = 'id', $format = null, $selectField = null)
    {
        $this->connection();
        $format = strtolower($format);
        if (!empty($selectField)) {
            if (!is_array($selectField)) {
                $selectField = array($selectField);
            }
        } else {
            $selectField = array('*');
        }
        $db = $this->db->select($selectField)->from($this->table);
        if (!empty($value)) {
            if (is_array($value) && count($value) > 0) {
                foreach ($value as $f => $v) {
                    if (is_array($v)) {
                        $db->where(new Conditional($f, self::OPERATOR_IS_IN, $v));
                    } else {
                        $db->where(new Conditional($f, self::OPERATOR_EQUAL_TO, $v));
                    }
                }
            } else {
                $db->where(new Conditional($field, self::OPERATOR_EQUAL_TO, $value));
            }
        }
        if ($format === 'result') {
            $result = $db->execute()->fetchAll();
            //$this->logger->debug(__FUNCTION__, 'Format is get all Result => ' . json_encode($result));
        } else {
            $result = $db->execute()->fetch();
            //$this->logger->debug(__FUNCTION__, 'Format is get first Result => ' . json_encode($result));
        }
        //$this->logger->debug(__FUNCTION__, 'GET Result => ' . json_encode($result));
        if ($format === 'json') {
            //$this->logger->debug(__FUNCTION__, 'Output Result is Json');

            return json_encode($result);
        }

        return $result;
    }

    /**
     * Function getInfoWithMultipleWhere
     *
     * @param string|array $wheres
     * @param string       $field
     * @param null         $format
     * @param null         $selectField
     *
     * @return array|false|mixed|string
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 56:15
     */
    public function getInfoWithMultipleWhere($wheres = '', $field = 'id', $format = null, $selectField = null)
    {
        $this->connection();
        $format = strtolower($format);
        if (!empty($selectField)) {
            if (!is_array($selectField)) {
                $selectField = array($selectField);
            }
        } else {
            $selectField = array('*');
        }
        $db = $this->db->select($selectField)->from($this->table);
        if (!empty($wheres)) {
            if (is_array($wheres) && count($wheres) > 0) {
                foreach ($wheres as $value) {
                    if (is_array($value['value'])) {
                        $db->where(new Conditional($value['field'], self::OPERATOR_IS_IN, $value['value']));
                    } else {
                        $db->where(new Conditional($value['field'], $value['operator'], $value['value']));
                    }
                }
            } else {
                $db->where(new Conditional($field, self::OPERATOR_EQUAL_TO, $wheres));
            }
        }

        if ($format === 'result') {
            $result = $db->execute()->fetchAll();
            //$this->logger->debug(__FUNCTION__, 'Format is get all Result => ' . json_encode($result));
        } else {
            $result = $db->execute()->fetch();
            //$this->logger->debug(__FUNCTION__, 'Format is get first Result => ' . json_encode($result));
        }
        //$this->logger->debug(__FUNCTION__, 'GET Result => ' . json_encode($result));
        if ($format === 'json') {
            //$this->logger->debug(__FUNCTION__, 'Output Result is Json');

            return json_encode($result);
        }

        return $result;
    }

    /**
     * Function getValue
     *
     * @param string|array $value
     * @param string       $field
     * @param string|array $fieldOutput
     *
     * @return   mixed|null
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 58:06
     */
    public function getValue($value = '', $field = 'id', $fieldOutput = '')
    {
        $this->connection();
        if (!is_array($fieldOutput)) {
            $fieldOutput = array($fieldOutput);
        }
        $db = $this->db->select($fieldOutput)->from($this->table);
        if (!empty($value)) {
            if (is_array($value) && count($value) > 0) {
                foreach ($value as $column => $column_value) {
                    if (is_array($column_value)) {
                        $db->where(new Conditional($column, self::OPERATOR_IS_IN, $column_value));
                    } else {
                        $db->where(new Conditional($column, self::OPERATOR_EQUAL_TO, $column_value));
                    }
                }
            } else {
                $db->where(new Conditional($field, self::OPERATOR_EQUAL_TO, $value));
            }
        }
        $result = $db->execute()->fetch();

        //$this->logger->debug(__FUNCTION__, 'GET Result => ' . json_encode($result));
        if (isset($result->$fieldOutput)) {
            return $result->$fieldOutput;
        }

        return null;
    }

    /**
     * Function getValueWithMultipleWhere
     *
     * @param string|array $wheres
     * @param string       $field
     * @param string|array $fieldOutput
     *
     * @return   mixed|null
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 58:54
     */
    public function getValueWithMultipleWhere($wheres = array(), $field = 'id', $fieldOutput = '')
    {
        $this->connection();
        if (!is_array($fieldOutput)) {
            $fieldOutput = array($fieldOutput);
        }
        $db = $this->db->select($fieldOutput)->from($this->table);
        if (!empty($wheres)) {
            if (is_array($wheres) && count($wheres) > 0) {
                foreach ($wheres as $value) {
                    if (is_array($value['value'])) {
                        $db->where(new Conditional($value['field'], self::OPERATOR_IS_IN, $value['value']));
                    } else {
                        $db->where(new Conditional($value['field'], $value['operator'], $value['value']));
                    }
                }
            } else {
                $db->where(new Conditional($field, self::OPERATOR_EQUAL_TO, $wheres));
            }
        }
        $result = $db->execute()->fetch();

        //$this->logger->debug(__FUNCTION__, 'GET Result => ' . json_encode($result));
        if (isset($result->$fieldOutput)) {
            return $result->$fieldOutput;
        }

        return null;
    }

    /**
     * Hàm lấy danh sách Distinct toàn bộ bản ghi trong 1 bảng
     *
     * @param string|array $selectField Mảng dữ liệu danh sách các field cần so sánh
     *
     * @return array
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 59:24
     */
    public function getDistinctResult($selectField = '')
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = array($selectField);
        }
        $db = $this->db->select($selectField)->from($this->table)->distinct();

        //$this->logger->debug(__FUNCTION__, 'Result from DB => ' . json_encode($result));

        return $db->execute()->fetchAll();
    }

    /**
     * Function getResultDistinct - Hàm getResultDistinct là alias của hàm getDistinctResult
     *
     * Các tham số đầu ra và đầu vào theo quy chuẩn của hàm getDistinctResult
     *
     * @param string $selectField Mảng dữ liệu danh sách các field cần so sánh
     *
     * @return array
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 59:37
     */
    public function getResultDistinct($selectField = '')
    {
        return $this->getDistinctResult($selectField);
    }

    /**
     * Function getResult
     *
     * @param array        $wheres
     * @param string|array $selectField
     * @param null         $options
     *
     * @return array
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 59:54
     */
    public function getResult($wheres = array(), $selectField = '*', $options = null)
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = array($selectField);
        }
        $db = $this->db->select($selectField)->from($this->table);
        if (!empty($wheres)) {
            if (is_array($wheres) && count($wheres) > 0) {
                foreach ($wheres as $column => $column_value) {
                    if (is_array($column_value)) {
                        $db->where(new Conditional($column, self::OPERATOR_IS_IN, $column_value));
                    } else {
                        $db->where(new Conditional($column, self::OPERATOR_EQUAL_TO, $column_value));
                    }
                }
            } else {
                $db->where(new Conditional($this->primaryKey, self::OPERATOR_EQUAL_TO, $wheres));
            }
        }
        if (isset($options['limit'], $options['offset']) && $options['limit'] > 0) {
            $page = $this->preparePaging($options['offset'], $options['limit']);
            $db->limit(new Limit($page['limit'], $page['offset']));
        }
        if (isset($options['orderBy']) && is_array($options['orderBy'])) {
            foreach ($options['orderBy'] as $column => $direction) {
                $db->orderBy($column, $direction);
            }
        }

        // $this->logger->debug(__FUNCTION__, 'Format is get all Result => ' . json_encode($result));

        return $db->execute()->fetchAll();
    }

    /**
     * Function getResultWithMultipleWhere
     *
     * @param array        $wheres
     * @param string|array $selectField
     * @param array|null   $options
     *
     * @return array
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 47:38
     */
    public function getResultWithMultipleWhere($wheres = array(), $selectField = '*', $options = null)
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = array($selectField);
        }
        $db = $this->db->select($selectField)->from($this->table);
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
        if (isset($options['limit'], $options['offset']) && $options['limit'] > 0) {
            $page = $this->preparePaging($options['offset'], $options['limit']);
            $db->limit(new Limit($page['limit'], $page['offset']));
        }
        if (isset($options['orderBy']) && is_array($options['orderBy'])) {
            foreach ($options['orderBy'] as $column => $direction) {
                $db->orderBy($column, $direction);
            }
        }

        // $this->logger->debug(__FUNCTION__, 'Format is get all Result => ' . json_encode($result));

        return $db->execute()->fetchAll();
    }

    /**
     * Function countResult
     *
     * @param array        $wheres
     * @param string|array $selectField
     *
     * @return int
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 48:26
     */
    public function countResult($wheres = array(), $selectField = '*')
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = array($selectField);
        }
        $db = $this->db->select($selectField)->from($this->table);


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
            } else {
                $db->where(new Conditional($this->primaryKey, self::OPERATOR_EQUAL_TO, $wheres));
            }
        }

        return $db->execute()->rowCount();
    }

    /**
     * Function add
     *
     * @param array $data
     *
     * @return int
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 19/06/2022 20:59
     */
    public function add(array $data = array())
    {
        $this->connection();
        $result = $this->db->insert($data)->into($this->table)->execute();
        if ($result !== false) {
            return $result->rowCount();
        }

        return 0;
    }

    /**
     * Function update
     *
     * @param array $data
     * @param array $wheres
     *
     * @return int
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 19/06/2022 20:20
     */
    public function update(array $data = array(), array $wheres = array())
    {
        $this->connection();
        $db = $this->db->update($data);

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
            } else {
                $db->where(new Conditional($this->primaryKey, self::OPERATOR_EQUAL_TO, $wheres));
            }
        }
        $result = $db->execute();
        //$this->logger->debug(__FUNCTION__, 'Result Update Rows: ' . $result);
        if ($result !== false) {
            return $result->rowCount();
        }

        return 0;
    }

    /**
     * Function delete
     *
     * @param array $wheres
     *
     * @return int
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 19/06/2022 19:51
     */
    public function delete(array $wheres = array())
    {
        $this->connection();
        $db = $this->db->delete($this->table);
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
            } else {
                $db->where(new Conditional($this->primaryKey, self::OPERATOR_EQUAL_TO, $wheres));
            }
        }
        $result = $db->execute();
        //$this->logger->debug(__FUNCTION__, 'Result Delete Rows: ' . $result);
        if ($result !== false) {
            return $result->rowCount();
        }

        return 0;
    }
}
