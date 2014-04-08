<?php
class ResultsExtractor {

    /**
     * @param string $doc
     *
     * @return domDocument
     */
    private static function prepareDoc($doc)
    {
        $dom = new domDocument;

        @$dom->loadHTML($doc);
        $dom->preserveWhiteSpace = false;

        return $dom;
    }

    public static function FromMarkbook($doc)
    {
        $dom = self::prepareDoc((string) $doc);

        $finder    = new DomXPath($dom);
        $tables      = $finder->query("//table[contains(@class, 'day')]");

        $results = [];

        $tdMap = [0 => 'num', 1 => 'discipline', 2 => 'hometask', 3 => 'marks'];

        foreach ($tables as $t => $table)
        {
            $caption = $finder->query(".//caption/h4", $table);

            $day = $caption->item(0)->textContent;

            $rows = $finder->query(".//tr", $table);

            foreach ($rows as $r => $row)
            {
                if($r <= 0)
                {
                    continue;
                }
                $tds = $finder->query(".//td", $row);
                foreach ($tds as $c => $td)
                {
                    if(!isset($tdMap[$c]))
                    {
                        continue;
                    }
                    $val = trim($td->textContent, " \r\n.");
                    if($tdMap[$c] == 'marks')
                    {
                        $val = [];
                        $markDiv = $finder->query(".//div", $td);
                        if($markDiv->length > 0)
                        {
                            foreach ($markDiv->item(0)->childNodes as $mark)
                            {
                                if(!empty($mark->textContent))
                                {
                                    $m = trim($mark->textContent, " \r\n");
                                    if(!empty($m))
                                    {
                                        $val[] = $m;
                                    }
                                }
                            }
                        }
                    }
                    $results[$day][$r-1][$tdMap[$c]] = $val;
                }
            }
        }
        return $results;
    }

    // Not implemented really
    public static function  FromRegister(string $doc)
    {
        $dom = self::prepareDoc($doc);

        $finder    = new DomXPath($dom);
        $rows      = $finder->query("//table[contains(@class, 'result')]/tr");

        foreach ($rows as $r => $row)
        {
            if ($r <= 2)
            {
                continue;
            }
            $cols = $row->childNodes;
            foreach ($cols as $k => $td)
            {
                echo $td->textContent;
            }
        }

    }

} 