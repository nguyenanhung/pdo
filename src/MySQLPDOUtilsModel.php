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
    }

    /**
     * Function rawExecStatement
     *
     * @param string $statement
     *
     * @return bool
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 08/28/2021 34:45
     */
    public function rawExecStatement(string $statement = ''): bool
    {
        try {
            $this->connection();
            $this->db->exec($statement);

            return TRUE;
        }
        catch (Exception $e) {
            $this->debug->error(__FUNCTION__, 'Error Message: ' . $e->getMessage());
            $this->debug->error(__FUNCTION__, 'Error Trace As String: ' . $e->getTraceAsString());

            return FALSE;
        }
    }
}
