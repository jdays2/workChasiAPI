<?php

namespace Site\Api\Services;

require_once($_SERVER["DOCUMENT_ROOT"]."/local/composer/src/HighloadBlock/WatchHighLoadBlock.php");

use Bitrix\Iblock\Iblock;
use Bitrix\Main\Context;
use Bitrix\Main\FileTable;
use Bitrix\Main\Loader;
use Bitrix\Main\UserFieldTable;
use Bitrix\Main\UserTable;
use Lib\HighloadBlock\WatchHighloadBlock;

Loader::includeModule('highloadblock');
Loader::includeModule('iblock');

use Bitrix\Highloadblock as HL;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\SystemException;
use Site\Api\Entity\UserFieldEnumTable;
use Site\Api\Enum\FieldType;
use Site\Api\Enum\FilterType;
use Site\Api\Exceptions\FilterException;


class AdService extends ServiceBase
{

    private const AD_HL_ID = 1;
    protected const FIELDS = [
        "id" => array(
            "type"=> FieldType::SCALAR
        ),
        "brand" => array(
            "field"=>"UF_BRAND",
            "type"=> FieldType::IB_EL,
            "ref_id" => 1,
            "ref_field" => "ID",
            "fields" => ["ID", "NAME", "CODE"],
            "filter_type" => FilterType::ARRAY,
            "filter_name" => "ID",
            "createable" => true
        ),
        "model" => array(
            "field"=>"UF_MODEL",
            "type"=> FieldType::SCALAR,
            "createable" => true
        ),
        "condition" => array(
            "field"=>"UF_SOST",
            "type"=>FieldType::ULIST,
            "filter_type" => FilterType::ARRAY,
            "createable" => true
        ),
        "gender" => array(
            "field"=>"UF_POL",
            "type"=>FieldType::ULIST,
            "filter_type" => FilterType::ARRAY,
            "createable" => true
        ),
        "year" => array(
            "field"=>"UF_GOD",
            "type"=>FieldType::SCALAR,
            "filter_type" => FilterType::RANGE_INT,
            "createable" => true
        ),
        "mechanism" => array(
            "field"=>"UF_MEXANIZM",
            "type"=>FieldType::ULIST,
            "filter_type" => FilterType::ARRAY,
            "createable" => true
        ),
        "frame_color" => array(
            "field"=>"UF_COLOR",
            "type"=> FieldType::IB_EL,
            "ref_id" => 2,
            "ref_field" => "ID",
            "fields" => ["ID","NAME", "COLOR"=>"HEX.VALUE"],
            "filter_type" => FilterType::ARRAY,
            "filter_name" => "ID",
            "createable" => true
        ),
        "dial_color" => array(
            "field"=>"UF_COLOR_CIFER",
            "type"=>FieldType::IB_EL,
            "ref_id" => 2,
            "ref_field" => "ID",
            "fields" => ["ID","NAME", "COLOR"=>"HEX.VALUE"],
            "filter_type" => FilterType::ARRAY,
            "filter_name" => "ID",
            "createable" => true
        ),
        "country" => array(
            "field" => "UF_COUNTRY",
            "type" => FieldType::IB_EL,
            "ref_id" => 3,
            "ref_field" => "ID",
            "fields" => ["ID", "NAME"],
            "filter_type" => FilterType::ARRAY,
            "filter_name" => "ID",
            "createable" => true
        ),
        "seller_type" => array(
            "field" => "UF_PRODAVEC",
            "type" => FieldType::ULIST,
            "filter_type" => FilterType::ARRAY,
            "createable" => true
        ),
        "material" => array(
            "field" => "UF_MATERIAL",
            "type" => FieldType::ULIST,
            "filter_type" => FilterType::ARRAY,
            "createable" => true
        ),
        "form" => array(
            "field" => "UF_FORMA",
            "type" => FieldType::ULIST,
            "filter_type" => FilterType::ARRAY,
            "createable" => true
        ),
        "size" => array(
            "field" => "UF_RAZMER",
            "type" => FieldType::ULIST,
            "filter_type" => FilterType::ARRAY,
            "createable" => true
        ),
        "watchband" => array(
            "field" => "UF_REMEN",
            "type" => FieldType::ULIST,
            "filter_type" => FilterType::ARRAY,
            "createable" => true
        ),
        "clasp" => array(
            "field" => "UF_ZASTEZHKA",
            "type" => FieldType::ULIST,
            "filter_type" => FilterType::ARRAY,
            "createable" => true
        ),
        "dial" => array(
            "field" => "UF_CIFERBLAT",
            "type" => FieldType::ULIST,
            "filter_type" => FilterType::ARRAY,
            "createable" => true
        ),
        "water_protection" => array(
            "field" => "UF_VODO",
            "type" => FieldType::ULIST,
            "filter_type" => FilterType::ARRAY,
            "createable" => true
        ),
        "description" => array(
            "field" => "UF_DESC",
            "type" => FieldType::SCALAR,
            "createable" => true
        ),
        "price" => array(
            "field" => "UF_PRICE",
            "type" => FieldType::SCALAR,
            "filter_type" => FilterType::RANGE_FLOAT,
            "createable" => true
        ),
        "photo" => array(
            "field" => "UF_FOTO",
            "type" => FieldType::PHOTO,
            "createable" => true
        ),
        "city" => array(
            "field" => "UF_TOWN",
            "type" => FieldType::SCALAR,
            "filter_type" => FilterType::ARRAY
        ),
        "user_id" => array(
            "field" => "UF_USER_ID",
            "type" => FieldType::SCALAR
        ),
        "promotion" => array(
            "field" => "UF_PROMOT",
            "type" => FieldType::BOOL,
            "createable" => true
        ),
        "promotion_type" => array(
            "field" => "UF_PROMOTION",
            "type" => FieldType::IB_EL,
            "ref_id" => 6,
            "ref_field" => "ID",
            "fields" => ["ID", "NAME"],
            "createable" => true
        ),
        "status" => array(
            "field" => "UF_STATUS",
            "type" => FieldType::ULIST
        ),
        "date_created" => array(
            "field" => "UF_CREATE_DATE",
            "type" => FieldType::SCALAR
        )
    ];

    /**
     * @return array
     */
    public function getList():array
    {
        $queryParams = $this->getQueryParams();
        $hlblock = HL\HighloadBlockTable::getById(self::AD_HL_ID)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();

        $dbElements = $entity_data_class::getList($queryParams);
        $elements = $dbElements->fetchAll();
        if(count($elements) && array_key_exists('photo', $elements[0])){
            $allPhotoIds = array_unique(array_reduce($elements, function ($carry, $item){
                return array_merge($carry, $item['photo']);
            }, []));
            $serverHost = (Context::getCurrent()->getRequest()->isHttps()?"https://":"http://").Context::getCurrent()->getServer()->getHttpHost();
            $files = FileTable::getList([
                "select"=>["ID","FULL_PATH"],
                "filter"=>["=ID"=>$allPhotoIds],
                "runtime"=>[
                    new ExpressionField('FULL_PATH', 'CONCAT("'.$serverHost.'/upload/", %s, "/", %s)', ["SUBDIR", "FILE_NAME"])
                ]
            ])->fetchAll();
            $files = array_reduce($files, function($carry, $item){
                $carry[$item["ID"]] = $item['FULL_PATH'];
                return $carry;
            });
            foreach($elements as &$element){
                foreach($element["photo"] as &$photo){
                    $photo = strval($photo);
                    $photo = $files[$photo];
                }
            }
        }
        $this->addPaginationParams($dbElements);
        return $elements;
    }

    public function create():\Bitrix\Main\Entity\AddResult
    {
        global $USER;
        $createData = $this->getCreateData();
        //убрать при модерации
        $createData["UF_ACTIVE"] = "Y";
        $city = UserTable::getById($USER->GetID())->fetch()["PERSONAL_CITY"];
        $createData["UF_TOWN"] = $city;
        $wh = new WatchHighloadBlock();
        return $wh->create($createData);
    }

    public function getCreateValues(){
        $createValues = [];
        foreach(self::FIELDS as $alias => $field){
            if(array_key_exists("createable", $field) && $field["createable"]){
                switch ($field["type"]){
                    case FieldType::IB_EL:{
                        $createValues[$alias] = Iblock::wakeUp($field["ref_id"])->getEntityDataClass()::getList([
                            "select" => $field["fields"],
                            "order" => ["ID" => "ASC"]
                        ])->fetchAll();
                        break;
                    }
                    case FieldType::ULIST:{
                        $createValues[$alias] = UserFieldEnumTable::getList([
                            "select" => ["ID", "VALUE"],
                            "filter" => ["USER_FIELD.FIELD_NAME" => $field["field"]],
                            "runtime" => [
                                "USER_FIELD" => [
                                    "data_type" => UserFieldTable::class,
                                    "reference" => [
                                        "=this.USER_FIELD_ID" => "ref.ID"
                                    ],
                                    ["join_type"=>"left"]
                                ]
                            ]
                        ])->fetchAll();
                    }
                }
            }
        }
        return $createValues;
    }

    public function getFilter(){
        $filter = [];
        $hlblock = HL\HighloadBlockTable::getById(self::AD_HL_ID)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        foreach(self::FIELDS as $alias => $field){
            if(array_key_exists("filter_type", $field)){
                switch ($field["filter_type"]){
                    case FilterType::ARRAY:{
                        switch ($field["type"]){
                            case FieldType::IB_EL:{
                                $select = [];
                                foreach($field["fields"] as $field_alias=>$field_name){
                                    if(is_int($field_alias)){
                                        $select[$alias."|".$field_name] = $alias.".".$field_name;
                                    }
                                    else{
                                        $select[$alias."|".$field_alias] = $alias.".".$field_name;
                                    }
                                }
                                $filter = array_merge($filter, $entity_data_class::getList([
                                    "select" => $select,
                                    "group" => [$field["field"]],
                                    "filter" => ["!".$field["field"] => ''],
                                    "runtime" => [
                                        $alias => [
                                            'data_type' => Iblock::wakeUp($field["ref_id"])->getEntityDataClass(),
                                            'reference' => [
                                                '=this.'.$field["field"] => 'ref.'.$field["ref_field"]
                                            ],
                                            ['join_type' => 'left']
                                        ]
                                    ],
                                    "order" => [$field["field"] => "ASC"]
                                ])->fetchAll());
                                break;
                            }
                            case FieldType::ULIST:{
                                $filter = array_merge($filter, $entity_data_class::getList([
                                    "select" => [$alias."|ID" => $field["field"], $alias."|NAME"=>$field["field"]."_ALIAS.VALUE"],
                                    "filter" => ["!".$field["field"] => ''],
                                    "order" => [$field["field"] => "ASC"],
                                    "group" => [$field["field"]],
                                    "runtime" => [
                                        $field["field"]."_ALIAS" => [
                                            'data_type' => UserFieldEnumTable::class,
                                            'reference' => [
                                                '=this.'.$field["field"] => 'ref.ID'
                                            ],
                                            ['join_type' => 'left']
                                        ]
                                    ]
                                ])->fetchAll());
                                break;
                            }
                            case FieldType::SCALAR:{
                                $filter = array_merge($filter, $entity_data_class::getList([
                                    "select" => [$alias => $field["field"]],
                                    "filter" => ["!".$field["field"] => ''],
                                    "order" => [$field["field"] => "ASC"],
                                    "group" => [$field["field"]]
                                ])->fetchAll());
                            }
                        }
                        break;
                    }
                    case FilterType::RANGE_INT:case FilterType::RANGE_FLOAT:{
                        switch ($field["type"]){
                            case FieldType::SCALAR:{
                                $filter = array_merge($filter, $entity_data_class::getList([
                                    "select" => [$alias."|MIN"=>"MIN", $alias."|MAX"=>"MAX"],
                                    "filter" => ["!".$field["field"] => ''],
                                    "runtime" => [
                                        new ExpressionField("MIN", "MIN(%s)", [$field["field"]]),
                                        new ExpressionField("MAX", "MAX(%s)", [$field["field"]])
                                    ]
                                ])->fetchAll());
                                break;
                            }
                        }
                        break;
                    }
                }
            }
        }
        return $filter;
    }
}