<?php

class ClassMain
{
    public function __construct()
    {

    }

    public function IncludeTemplate($Action = '', $data = null)
    {
        if (is_file($file = PATH.'/tpl/'.$Action.'.php')){
            include $file;
        } else {
            throw new Exception('Template /'.$Action.'.php'.' not exist');
        }
    }

    public function ajaxSet($Param)
    {
        $action       = !empty($Param['action'])? $Param['action'] : '';
        $is_login     = !empty($Param['auth'])? $Param['auth'] : 0;
        $id           = !empty($Param['id'])? $Param['id'] : null;
        $parent_id    = !empty($Param['parent_id'])? $Param['parent_id'] : 0;
        $publish_date = !empty($Param['publish_date'])? $Param['publish_date'] : '';
        $message      = !empty($Param['message'])? $Param['message'] : '';

        switch ($action)
        {
            case 'auth':
                $login = 'admin';
                $password = 'pass';
                if ($Param['login'] != $login || $Param['password'] != $password) {
                    echo 'false';
                }
                break;
            case 'count':
                echo self::CountXMLElements();
                break;
            case 'read':
                self::ReadXMLFile($is_login, $id, $parent_id);
                break;
            case 'new_message':
                self::AddNewMessage($id, $parent_id, $publish_date, $message);
                break;
            default:
                break;
        }

        die;
    }

    /**
     * @param $is_login - флаг авторизации
     * @param null $id - id записи
     * @param int $parent_id - id родительской записи
     * @throws Exception
     */
    public function ReadXMLFile ($is_login, $id = 0, $parent_id = 0)
    {
        /*
        $xmlr = new StoreXMLReader();
        //$r = $xmlr->parse(PATH.'/upload/example.xml');
        $r = $xmlr->parse(GB);
        */

        $doc = new DOMDocument();
        if (@$doc->load(GB) === false) {
            throw new Exception('Cound\'t load file: "'.GB.'"!');
        }

        $data = [];
        $data['is_login'] = !empty($is_login)? $is_login : 0;
        $data['id'] = (isset($id) && $id != 'undefined')? $id : 0;
        $data['parent_id'] = (isset($parent_id) && $parent_id != 'undefined')? $parent_id : 0;

        if ($data['id'] == 0 && $data['parent_id'] == 0) {
            $books = $doc->getElementsByTagName("book");
            foreach ($books as $book) {
                $publishers = $book->getElementsByTagName("publish_date");
                $data['publish_date'] = $publishers->item(0)->nodeValue;

                $messages = $book->getElementsByTagName("message");
                $data['message'] = $messages->item(0)->nodeValue;

                $this->IncludeTemplate('list', $data);
            }
        }
    }

    public function AddNewMessage($id, $parent_id, $publish_date, $message)
    {
        $doc = new DOMDocument();
        if (@$doc->load(GB) === false) {
            throw new Exception('Cound\'t load file: "'.GB.'"!');
        }

        $xpath = new DOMXPath($doc);
        $parent = $xpath->query ('//catalog');
        $next = $xpath->query ('//catalog/book');

        $new_book = $doc->createElement ('book');

        $new_id = $doc->createElement ('id', $id);
        $new_parent_id = $doc->createElement ('parent_id', $parent_id);
        $new_publish_date = $doc->createElement ('publish_date', $publish_date);
        $new_message = $doc->createElement ('message', $message);

        $new_book->appendChild ($new_id);
        $new_book->appendChild ($new_parent_id);
        $new_book->appendChild ($new_publish_date);
        $new_book->appendChild ($new_message);
        $parent->item(0)->insertBefore($new_book, $next->item(0));

        /*
        $book = $doc->appendChild($doc->createElement('book'));

        $data_id = $book->appendChild($doc->createElement('id'));
        $data_id->appendChild($doc->createTextNode($id));

        $data_parent_id = $book->appendChild($doc->createElement('parent_id'));
        $data_parent_id->appendChild($doc->createTextNode($parent_id));

        $data_publish_date = $book->appendChild($doc->createElement('publish_date'));
        $data_publish_date->appendChild($doc->createTextNode($publish_date));

        $data_message = $book->appendChild($doc->createElement('message'));
        $data_message->appendChild($doc->createTextNode($message));

        $doc->formatOutput = true;

        $gb = $doc->saveXML();
        */

        $doc->save(PATH.'/upload/gb.xml');



    }

    public function CountXMLElements()
    {
        $doc = new DOMDocument();
        if (@$doc->load(GB) === false) {
            throw new Exception('Cound\'t load file: "'.GB.'"!');
        }

        return $doc->getElementsByTagName('book')->length;
    }
}

function pa($array)
{
    $args = func_get_args();
    if(count($args) > 1) {
        foreach ($args as $values) {
            pa($values);
        }
    } else {
        if (is_array($array) || is_object($array)) {
            echo "<pre>";
            print_r($array);
            echo "</pre>";
        } else {
            echo $array;
        }
    }
}