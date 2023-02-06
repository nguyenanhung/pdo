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
    use Support;

    const VERSION = '3.0.8';
    const LAST_MODIFIED = '2023-01-06';
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
    public function __construct(array $database = [])
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
    public function getVersion(): string
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
    public function setPrimaryKey($primaryKey): MySQLPDOBaseModel
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
    public function getPrimaryKey(): string
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
    public function preparePaging(int $pageIndex = 1, int $pageSize = 10): array
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
    public function setDatabase(array $database = array(), string $name = 'default'): MySQLPDOBaseModel
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
    public function setTable(string $table = ''): MySQLPDOBaseModel
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
    public function connection(): MySQLPDOBaseModel
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
    public function disconnect(): MySQLPDOBaseModel
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
    public function getDb(): Database
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
    public function countAll($select = ['id']): int
    {
        $this->connection();

        //$this->logger->debug(__FUNCTION__, 'Total Result: ' . $total);
        return $this->db->select($select)->from($this->table)->execute()->rowCount();
    }

    /**
     * Function checkExists - Hàm kiểm tra sự tồn tại bản ghi theo tham số đầu vào
     *
     * @param string|array      $wheres Giá trị cần kiểm tra
     * @param string|null       $fields Field tương ứng, ví dụ: ID
     * @param string|array|null $select Bản ghi cần chọn
     *
     * @return int Số lượng bàn ghi tồn tại phù hợp với điều kiện đưa ra
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/16/18 11:45
     */
    public function checkExists($wheres = '', string $fields = 'id', $select = array('*')): int
    {
        return $this->checkExistsWithMultipleWhere($wheres, $fields, $select);
    }

    /**
     * Hàm kiểm tra sự tồn tại bản ghi theo tham số đầu vào - Đa điều kiện
     *
     * @param string|array      $wheres Giá trị cần kiểm tra
     * @param string|null       $fields Field tương ứng, ví dụ: ID
     * @param string|array|null $select Bản ghi cần chọn
     *
     * @return int Số lượng bàn ghi tồn tại phù hợp với điều kiện đưa ra
     * @author    : 713uk13m <dev@nguyenanhung.com>
     * @copyright : 713uk13m <dev@nguyenanhung.com>
     * @time      : 10/16/18 11:45
     */
    public function checkExistsWithMultipleWhere($wheres = '', string $fields = 'id', $select = array('*')): int
    {
        $this->connection();
        $db = $this->db->select($select)->from($this->table);

        if (!empty($wheres)) {
            if (is_array($wheres) && count($wheres) > 0) {
                foreach ($wheres as $columnField => $value) {
                    if (isset($value['operator'])) {
                        if (is_array($value['value'])) {
                            $db->where(new Conditional($value['field'], self::OPERATOR_IS_IN, $value['value']));
                        } else {
                            $db->where(new Conditional($value['field'], $value['operator'], $value['value']));
                        }
                    } else {
                        if (is_array($value)) {
                            $db->where(new Conditional($columnField, self::OPERATOR_IS_IN, $value));
                        } else {
                            $db->where(new Conditional($columnField, self::OPERATOR_EQUAL_TO, $value));
                        }
                    }
                }
            } else {
                $db->where(new Conditional($fields, self::OPERATOR_EQUAL_TO, $wheres));
            }
        }

        return $db->execute()->rowCount();
    }

    /**
     * Function getLatest - Hàm lấy bản ghi mới nhất theo điều kiện
     *
     * Mặc định giá trị so sánh dựa trên column created_at
     *
     * @param string|array $selectField Danh sách các column cần lấy
     * @param string       $byColumn    Column cần so sánh dữ liệu, mặc định sẽ sử dụng column created_at
     *
     * @return mixed
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 53:20
     */
    public function getLatest($selectField = array('*'), string $byColumn = 'created_at')
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = [$selectField];
        }
        $db = $this->db->select($selectField)->from($this->table);
        $db->orderBy($byColumn, self::ORDER_DESCENDING)->limit(new Limit(1));

        // $this->logger->debug(__FUNCTION__, 'GET Result => ' . json_encode($result));
        return $db->execute()->fetch();
    }

    /**
     * Function getLatestByColumn - Hàm lấy bản ghi mới nhất theo điều kiện sâu hơn
     *
     * @param string|array $wheres
     * @param string|array $selectField
     * @param string       $column
     * @param string       $fields
     *
     * @return mixed
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/06/2022 28:55
     */
    public function getLatestByColumn($wheres = array(), $selectField = '*', string $column = 'id', string $fields = 'id')
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = [$selectField];
        }
        $db = $this->db->select($selectField)->from($this->table);
        if (!empty($wheres)) {
            if (is_array($wheres) && count($wheres) > 0) {
                foreach ($wheres as $columnField => $value) {
                    if (isset($value['operator'])) {
                        if (is_array($value['value'])) {
                            $db->where(new Conditional($value['field'], self::OPERATOR_IS_IN, $value['value']));
                        } else {
                            $db->where(new Conditional($value['field'], $value['operator'], $value['value']));
                        }
                    } else {
                        if (is_array($value)) {
                            $db->where(new Conditional($columnField, self::OPERATOR_IS_IN, $value));
                        } else {
                            $db->where(new Conditional($columnField, self::OPERATOR_EQUAL_TO, $value));
                        }
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
     * @param string|array $selectField Danh sách các column cần lấy
     * @param string       $byColumn    Column cần so sánh dữ liệu, mặc định sẽ sử dụng column created_at
     *
     * @return mixed
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 54:15
     */
    public function getOldest($selectField = array('*'), string $byColumn = 'created_at')
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = [$selectField];
        }
        $db = $this->db->select($selectField)->from($this->table);
        $db->orderBy($byColumn, self::ORDER_ASCENDING)->limit(new Limit(1));

        // $this->logger->debug(__FUNCTION__, 'GET Result => ' . json_encode($result));

        return $db->execute()->fetch();
    }

    /**
     * Function getOldestByColumn - Hàm lấy bản ghi cũ nhất theo điều kiện sâu hơn
     *
     * @param string|array $wheres
     * @param string|array $selectField
     * @param string       $column
     * @param string       $fields
     *
     * @return mixed
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/06/2022 28:55
     */
    public function getOldestByColumn($wheres = array(), $selectField = '*', string $column = 'id', string $fields = 'id')
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = [$selectField];
        }
        $db = $this->db->select($selectField)->from($this->table);

        if (!empty($wheres)) {
            if (is_array($wheres) && count($wheres) > 0) {
                foreach ($wheres as $columnField => $value) {
                    if (isset($value['operator'])) {
                        if (is_array($value['value'])) {
                            $db->where(new Conditional($value['field'], self::OPERATOR_IS_IN, $value['value']));
                        } else {
                            $db->where(new Conditional($value['field'], $value['operator'], $value['value']));
                        }
                    } else {
                        if (is_array($value)) {
                            $db->where(new Conditional($columnField, self::OPERATOR_IS_IN, $value));
                        } else {
                            $db->where(new Conditional($columnField, self::OPERATOR_EQUAL_TO, $value));
                        }
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
     * @param array|string $wheres      Giá trị cần kiểm tra
     * @param string|null  $field       Field tương ứng, ví dụ: ID
     * @param string|null  $format      Format dữ liệu đầu ra: null, json, array, base, result
     * @param mixed        $selectField Các field cần lấy
     *
     * @return object|array|string|null Mảng|String|Object dữ liều phụ hợp với yêu cầu map theo biến format truyền vào
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/16/18 11:51
     */
    public function getInfo($wheres = '', string $field = 'id', string $format = null, $selectField = null)
    {
        return $this->getInfoWithMultipleWhere($wheres, $field, $format, $selectField);
    }

    /**
     * Function getInfoWithMultipleWhere
     *
     * @param string|array $wheres
     * @param string|null  $field       Field tương ứng, ví dụ: ID
     * @param string|null  $format      Format dữ liệu đầu ra: null, json, array, base, result
     * @param mixed        $selectField Các field cần lấy
     *
     * @return array|false|mixed|string
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 56:15
     */
    public function getInfoWithMultipleWhere($wheres = '', string $field = 'id', string $format = null, $selectField = null)
    {
        $this->connection();
        $format = strtolower($format);

        if (!empty($selectField)) {
            if (!is_array($selectField)) {
                $selectField = [$selectField];
            }
        } else {
            $selectField = array('*');
        }
        $db = $this->db->select($selectField)->from($this->table);

        if (!empty($wheres)) {
            if (is_array($wheres) && count($wheres) > 0) {
                foreach ($wheres as $fields => $value) {
                    if (isset($value['operator'])) {
                        if (is_array($value['value'])) {
                            $db->where(new Conditional($value['field'], self::OPERATOR_IS_IN, $value['value']));
                        } else {
                            $db->where(new Conditional($value['field'], $value['operator'], $value['value']));
                        }
                    } else {
                        if (is_array($value)) {
                            $db->where(new Conditional($fields, self::OPERATOR_IS_IN, $value));
                        } else {
                            $db->where(new Conditional($fields, self::OPERATOR_EQUAL_TO, $value));
                        }
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
     * @param        $value
     * @param string $field
     * @param        $fieldOutput
     *
     * @return mixed|null
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 19/06/2022 35:20
     */
    public function getValue($value = '', string $field = 'id', $fieldOutput = '')
    {
        return $this->getValueWithMultipleWhere($value, $field, $fieldOutput);
    }

    /**
     * Function getValueWithMultipleWhere
     *
     * @param        $wheres
     * @param string $field
     * @param        $fieldOutput
     *
     * @return mixed|null
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 19/06/2022 34:59
     */
    public function getValueWithMultipleWhere($wheres = array(), string $field = 'id', $fieldOutput = '')
    {
        $this->connection();
        if (!is_array($fieldOutput)) {
            $fieldOutput = [$fieldOutput];
        }
        $db = $this->db->select($fieldOutput)->from($this->table);
        if (!empty($wheres)) {
            if (is_array($wheres) && count($wheres) > 0) {
                foreach ($wheres as $whereField => $value) {
                    if (isset($value['operator'])) {
                        if (is_array($value['value'])) {
                            $db->where(new Conditional($value['field'], self::OPERATOR_IS_IN, $value['value']));
                        } else {
                            $db->where(new Conditional($value['field'], $value['operator'], $value['value']));
                        }
                    } else {
                        if (is_array($value)) {
                            $db->where(new Conditional($whereField, self::OPERATOR_IS_IN, $value));
                        } else {
                            $db->where(new Conditional($whereField, self::OPERATOR_EQUAL_TO, $value));
                        }
                    }
                }
            } else {
                $db->where(new Conditional($field, self::OPERATOR_EQUAL_TO, $wheres));
            }
        }
        $result = $db->execute()->fetch();

        //$this->logger->debug(__FUNCTION__, 'GET Result => ' . json_encode($result));
        return $result->$fieldOutput ?? null;
    }

    /**
     * Hàm lấy danh sách Distinct toàn bộ bản ghi trong 1 bảng
     *
     * @param string|array $selectField Mảng dữ liệu danh sách các field cần so sánh
     * @param string|array $wheres      Điều kiện lấy dữ liệu
     *
     * @return array
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 59:24
     */
    public function getDistinctResult($selectField = '', $wheres = array()): array
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = [$selectField];
        }
        $db = $this->db->select($selectField)->from($this->table)->distinct();
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
                $db->where(new Conditional($this->primaryKey, self::OPERATOR_EQUAL_TO, $wheres));
            }
        }

        //$this->logger->debug(__FUNCTION__, 'Result from DB => ' . json_encode($result));

        return $db->execute()->fetchAll();
    }

    /**
     * Function getResultDistinct - Hàm getResultDistinct là alias của hàm getDistinctResult
     *
     * Các tham số đầu ra và đầu vào theo quy chuẩn của hàm getDistinctResult
     *
     * @param string       $selectField Mảng dữ liệu danh sách các field cần so sánh
     * @param string|array $wheres      Điều kiện lấy dữ liệu
     *
     * @return array
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 59:37
     */
    public function getResultDistinct(string $selectField = '', $wheres = array()): array
    {
        return $this->getDistinctResult($selectField, $wheres);
    }

    /**
     * Function getResult
     *
     * @param string|array $wheres
     * @param string|array $selectField
     * @param array|null   $options
     *
     * @return array
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 59:54
     */
    public function getResult($wheres = array(), $selectField = '*', $options = null): array
    {
        return $this->getResultWithMultipleWhere($wheres, $selectField, $options);
    }

    /**
     * Function getResultWithMultipleWhere
     *
     * @param string|array $wheres
     * @param string|array $selectField
     * @param array|null   $options
     *
     * @return array
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 47:38
     */
    public function getResultWithMultipleWhere($wheres = array(), $selectField = '*', $options = null): array
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = [$selectField];
        }
        $db = $this->db->select($selectField)->from($this->table);
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
     * Function countResult
     *
     * @param string|array $wheres
     * @param string|array $selectField
     *
     * @return int
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 48:26
     */
    public function countResult($wheres = array(), $selectField = '*'): int
    {
        $this->connection();
        if (!is_array($selectField)) {
            $selectField = [$selectField];
        }
        $db = $this->db->select($selectField)->from($this->table);

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
     * @time     : 19/06/2022 26:44
     */
    public function add(array $data = array()): int
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
     * @param array        $data
     * @param string|array $wheres
     *
     * @return int
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 50:08
     */
    public function update(array $data = array(), $wheres = array()): int
    {
        $this->connection();
        $db = $this->db->update($data);
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
     * @param string|array $wheres
     *
     * @return int
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 10/09/2020 50:03
     */
    public function delete($wheres = array()): int
    {
        $this->connection();
        $db = $this->db->delete($this->table);

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
