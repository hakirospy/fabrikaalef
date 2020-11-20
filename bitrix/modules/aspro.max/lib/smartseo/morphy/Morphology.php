<?php
namespace Aspro\Max\Smartseo\Morphy;

use Aspro\Max\Smartseo;

include_once __DIR__ . '/const.php';

class Morphology
{
    private static $instance;

    private $morphy = null;

    private $lang = 'ru_RU';

    private $words = [];

    function __construct()
    {
        require_once($this->getLibraryCommonFile());

        try {
            $this->morphy = new \phpMorphy($this->getDictionaryDir(), $this->getLang(), $this->getOptions());
        } catch (phpMorphy_Exception $e) {

        }
    }

    /**
     *
     * @return $this
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new Morphology();
        }

        return self::$instance;
    }

    public function libInstance()
    {
        return $this->morphy;
    }

    public function castWord($word, $grammems)
    {
        if($this->morphy === null) {
            return $word;
        }

        $_word = mb_strtoupper($word);

        $gramInfo = $this->getGramInfo($_word);

        if(!in_array($gramInfo['pos'], [SMARTSEO_MORPHOLOGY_NOUN, SMARTSEO_MORPHOLOGY_ADJ_FULL, SMARTSEO_MORPHOLOGY_ADJ_SHORT,
            SMARTSEO_MORPHOLOGY_VERB])) {
            return $word;
        }

        $result = $this->libInstance()->castFormByGramInfo($_word, null, $grammems, true);

        if($result && is_array($result)) {
            return mb_strtolower($result[0]);
        }
    }

    public function getGramInfo($word, $isFirst = true)
    {
        if($this->words[$word]) {
            return $this->words[$word];
        }

        if($this->morphy === null) {
            return $word;
        }

        $_word = mb_strtoupper($word);

        $result = $this->libInstance()->getGramInfo($_word);

        if(!$result) {
            return null;
        }

        if($isFirst) {
            $this->words[$_word] = $result[0][0];

            return $result[0][0];
        }

        $this->words[$_word] = $result;

        return $result;
    }

    public function getGender($word)
    {
        $result = $this->getGramInfo($word);

        if(in_array(SMARTSEO_MORPHOLOGY_MASCULINUM, $result['grammems'])) {
            return SMARTSEO_MORPHOLOGY_MASCULINUM;
        }

        if(in_array(SMARTSEO_MORPHOLOGY_FEMINUM, $result['grammems'])) {
            return SMARTSEO_MORPHOLOGY_FEMINUM;
        }

        if(in_array(SMARTSEO_MORPHOLOGY_NEUTRUM, $result['grammems'])) {
            return SMARTSEO_MORPHOLOGY_NEUTRUM;
        }

        if(in_array(SMARTSEO_MORPHOLOGY_SINGULAR, $result['grammems'])) {
            return SMARTSEO_MORPHOLOGY_SINGULAR;
        }

        if(in_array(SMARTSEO_MORPHOLOGY_PLURAL, $result['grammems'])) {
            return SMARTSEO_MORPHOLOGY_PLURAL;
        }
    }

    protected function getOptions()
    {
        return [
            'storage' => PHPMORPHY_STORAGE_FILE,
        ];
    }

    protected function getDictionaryDir()
    {
        $dictionary = null;
        if(mb_strtolower(LANG_CHARSET) === 'windows-1251') {
           $dictionary = 'windows-1251';
        } else {
            $dictionary = 'utf-8';
        }

        return  Smartseo\General\Smartseo::getModulePath() . 'vendors/phpmorphy/dictionary/' . $dictionary . '/';
    }

    protected function getLibraryCommonFile()
    {
        return Smartseo\General\Smartseo::getModulePath() . 'vendors/phpmorphy/phpmorphy-0.3.7/src/common.php';
    }

    protected function getLang()
    {
        return $this->lang;
    }
}
