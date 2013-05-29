<?php

Import::php("OpenM-Services.api.Impl.DAO.OpenM_DAO");
Import::php("OpenM-DAO.DB.OpenM_DB_Sequence");

/**
 * Description of OpenM_Book_DAO
 *
 * @package OpenM 
 * @subpackage OpenM\OpenM-Book\api\Impl\DAO  
 * @author nico
 */
class OpenM_Book_DAO extends OpenM_DAO {

    const DAO_CONFIG_FILE_NAME = "OpenM_Book.DAO.config.file.path";
    const DAO_PREFIX = "OpenM_Book.DAO.prefix";

    /**
     * @var OpenM_DB_Sequence
     */
    protected $sequence;

    public function __construct() {
        parent::__construct();
        $namePropertyTmp = $this->getSequencePropertyName();
        if ($namePropertyTmp !== null) {
            $p = Properties::fromFile(OpenM_ServiceImpl::CONFIG_FILE_NAME);
            $p2 = Properties::fromFile($p->get($this->getDaoConfigFileName()));
            $sequence = $p2->get($namePropertyTmp);
            if ($sequence == null)
                throw new OpenM_ServiceImplException("$namePropertyTmp not defined in " . $p->get($this->getDaoConfigFileName()));
            $this->sequence = new OpenM_DB_Sequence($sequence);
        }
    }

    public function getDaoConfigFileName() {
        return self::DAO_CONFIG_FILE_NAME;
    }

    public function getSequencePropertyName() {
        return NULL;
    }

    public function getPrefixPropertyName() {
        return self::DAO_PREFIX;
    }

}

?>