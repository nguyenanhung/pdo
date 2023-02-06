<?php
/**
 * Project pdo.
 * Created by PhpStorm.
 * User: 713uk13m <dev@nguyenanhung.com>
 * Date: 2021-08-28
 * Time: 10:21
 */

namespace nguyenanhung\PDO;

use Exception;

/**
 * Class MySQLPDOUtilsModel
 *
 * @package   nguyenanhung\PDO
 * @author    713uk13m <dev@nguyenanhung.com>
 * @copyright 713uk13m <dev@nguyenanhung.com>
 */
class MySQLPDOUtilsModel extends MySQLPDOBaseModel
{
    /**
     * PDOUtilsModel constructor.
     *
     * @param array $database
     *
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     */
    public function __construct(array $database = [])
    {
        parent::__construct($database);
        $this->database = $database;
        if ($this->debugStatus === true && class_exists('nguyenanhung\MyDebug\Logger')) {
            $this->logger->setLoggerSubPath(__CLASS__);
        }
        $this->debug = $this->logger;
    }

    /**
     * Function rawExecStatement
     *
     * @param string $statement
     *
     * @return bool
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 09/24/2021 59:32
     */
    public function rawExecStatement($statement = '')
    {
        try {
            $this->connection();
            $this->db->exec($statement);

            return true;
        } catch (Exception $e) {
            $this->logger->error(__FUNCTION__, 'Error Message: ' . $e->getMessage());
            $this->logger->error(__FUNCTION__, 'Error Trace As String: ' . $e->getTraceAsString());

            return false;
        }
    }
}
