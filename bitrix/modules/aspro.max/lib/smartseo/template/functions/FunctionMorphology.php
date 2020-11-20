<?php

namespace Aspro\Max\Smartseo\Template\Functions;

class FunctionMorphology extends \Bitrix\Iblock\Template\Functions\FunctionBase
{

    public function calculate(array $parameters)
	{
		$result = $this->parametersToArray($parameters);

        $case = mb_strtoupper(array_pop($result));
        $singularPlural = mb_strtoupper(array_pop($result));

        $words = $result;

        if(!$words) {
            return '';
        }

        $morphy = \Aspro\Max\Smartseo\Morphy\Morphology::getInstance();

        $result = null;

        foreach ($words as $word) {
            $collocationWords = explode(' ', $word);

            $gender = null;
            if(count($collocationWords) > 1) {
                $_lastCollocationWord = $collocationWords[count($collocationWords) - 1];
                $gender = $morphy->getGender($_lastCollocationWord);
            }

            $castWords = null;
            foreach ($collocationWords as $collocationWord) {
                if(!$gender) {
                    $gender = $morphy->getGender($collocationWord);
                }
                $castWords[] = $morphy->castWord($collocationWord, [$singularPlural, $case, $gender]);

            }

            $result[] = $castWords ? implode(' ', $castWords) : $word;
        }


        return $result;
	}
}
