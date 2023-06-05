<?php

class BfxTranslations
{
  public static $translations = [
    'en' => [
      'filter_by' => 'Filter by',
      'category' => 'Category',
      'sports_and_leisure' => 'Sports and Leisure',
      'services' => 'Services',
      'food_and_drink' => 'Food and Drink',
      'fashion' => 'Fashion',
      'entertainment' => 'Entertainment',
      'home_and_garden' => 'Home and Garden',
      'electronics' => 'Electronics',
      'retail' => 'Retail',
      'auto_and_moto' => 'Auto and Moto',
      'toys' => 'Toys',
      'other' => 'Other',
      'accepts' => 'Accepts',
      'accepted_tokens' => 'Accepted Tokens'

    ],
    'it' => [],
  ];

  public function __construct($lang)
  {
    $this->lang = $lang;
    $this->translations = self::$translations[$lang];
    $this->default_translations = self::$translations['en'];
  }

  public function translate($key)
  {
    return $this->translations[$key] ?? $this->default_translations[$key] ?? $key;
  }
}

?>
