<?php
class StoreXMLReader
{

    private $reader;
    private $tag;

    // if $ignoreDepth == 1 then will parse just first level, else parse 2th level too

    private function parseBlock($name, $ignoreDepth = 1) {
        if ($this->reader->name == $name && $this->reader->nodeType == XMLReader::ELEMENT) {
            $result = array();
            while (!($this->reader->name == $name && $this->reader->nodeType == XMLReader::END_ELEMENT)) {
                //echo $this->reader->name. ' - '.$this->reader->nodeType." - ".$this->reader->depth."\n";
                switch ($this->reader->nodeType) {
                    case 1:
                        if ($this->reader->depth > 3 && !$ignoreDepth) {
                            $result[$nodeName] = (isset($result[$nodeName]) ? $result[$nodeName] : array());
                            while (!($this->reader->name == $nodeName && $this->reader->nodeType == XMLReader::END_ELEMENT)) {
                                $resultSubBlock = $this->parseBlock($this->reader->name, 1);

                                if (!empty($resultSubBlock))
                                    $result[$nodeName][] = $resultSubBlock;

                                unset($resultSubBlock);
                                $this->reader->read();
                            }
                        }
                        $nodeName = $this->reader->name;
                        if ($this->reader->hasAttributes) {
                            $attributeCount = $this->reader->attributeCount;

                            for ($i = 0; $i < $attributeCount; $i++) {
                                $this->reader->moveToAttributeNo($i);
                                $result['attr'][$this->reader->name] = $this->reader->value;
                            }
                            $this->reader->moveToElement();
                        }
                        break;

                    case 3:
                    case 4:
                        $result[$nodeName] = $this->reader->value;
                        $this->reader->read();
                        break;
                }

                $this->reader->read();
            }
            return $result;
        }
    }

    public function parse($filename) {

        if (!$filename) return array();

        $this->reader = new XMLReader();
        $this->reader->open($filename);

        // begin read XML
        while (@$this->reader->read()) {

            if ($this->reader->name == 'book') {
                pa($this->reader->name);
                // while not found end tag read blocks
                while (!($this->reader->name == 'store_categories' && $this->reader->nodeType == XMLReader::END_ELEMENT)) {
                    $store_category = $this->parseBlock('store_category');

                    /*
                        Do some code
                    */

                    $this->reader->read();
                }

                $this->reader->read();
            }

        } // while
    } // func
}

/*
$xmlWriter = new XMLWriter();
$xmlWriter->openMemory();
$xmlWriter->startDocument('1.0', 'UTF-8');
$xmlWriter->startElement('shop');
for ($i=0; $i<=10; ++$i) {
    $productId = uniqid();

    $xmlWriter->startElement('product');
    $xmlWriter->writeElement('id', $productId);
    $xmlWriter->writeElement('name', 'Some product name. ID:' . $productId);
    $xmlWriter->endElement();
    // Flush XML in memory to file every 1000 iterations
    if (0 == $i%1000) {
        file_put_contents(PATH.'/upload/example.xml', $xmlWriter->flush(true), FILE_APPEND);
    }
}
$xmlWriter->endElement();
// Final flush to make sure we haven't missed anything
file_put_contents(PATH.'/upload/example.xml', $xmlWriter->flush(true), FILE_APPEND);
*/