<?php

namespace TH;

class LanguageDetect
{
  /**
   * Detect language of a text
   * 
   * @param string $text
   * @param array  $locales
   * @return array mapping each locale in $locales to number of words in $text which are spelled correctly according to the locale, the entry 'total' will be set to the total number of words.
   * @author Herbert Kruitbosch
   */
  static function detect($text, $locales) {
    $words = array();
    preg_match_all('/([a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|\xC5[\x92\x93\xA0\xA1\xB8\xBD\xBE]){2,}/', $text, $words, PREG_PATTERN_ORDER);
    
    $r = enchant_broker_init();
    $classification = array_combine($locales,
      array_map(
        function ($locale) use ($words,$r) {
          $dictionary = enchant_broker_request_dict($r, $locale);
          if ($dictionary === false)
              throw new \Exception("Dictionary " . $locale . " not installed, install the right aspell (or other enchant backend) package.");
          return  array_sum(
            array_map(
              function ($word) use ($dictionary) {
                return enchant_dict_check($dictionary, $word)  ? 1 : 0;
              }, $words[0]
            )
          );
        }, $locales
      )
    );
    enchant_broker_free($r);
    
    $classification["total"] = count($words[0]);
    
    return $classification;
  }
}
/*
  Example:

  echo json_encode(LanguageDetect::detect(
    'Loch Tanna is het grootste en meest afgelegen meer (loch) op Arran, een eiland behorend tot het Schotse raadsgebied North Ayrshire.
    De oppervlakte van het meer ligt 321 meter boven zeeniveau en de dichtstbijzijnde weg is meer dan 6 kilometer verderop.
    Tijdens de hoogtijdagen van de laatste ijstijd was het door ijs uitgegraven bassin van Loch Tanna de basis voor een grote ijskap dat de bergen van Arran bedekte. Deze ijskap wist de ijsstroom van de Schotse Hooglanden zodanig af te wenden dat granietresten van Arran gevonden zijn in de lagere gedeeltes van Ayrshire.',
    array("nl", "en")
  ));
*/
?>