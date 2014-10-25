<?php

class ResultsExtractor
{
    public static function FromMarkbook2014($doc)
    {
        $dom = self::prepareDoc((string)$doc);

        $finder = new DomXPath($dom);
        $table  = $finder->query("//table[contains(@class, 'data')]")->item(0);

        if (!$table)
        {
            return null;
        }

        $results = [];

        $tdMap = [
            0 => 'num',
            1 => 'discipline',
            2 => 'hometask',
            3 => 'marks',
            4 => 'num',
            5 => 'discipline',
            6 => 'hometask',
            7 => 'marks',
        ];

        $rows = $finder->query(".//tr", $table);

        $currentDate1    = "";
        $currentWeekday1 = "";
        $currentDate2    = "";
        $currentWeekday2 = "";

        foreach ($rows as $r => $row)
        {
            $rowDates = $finder->query(".//th[contains(@class, 'dnevnik_date')]", $row);
            if ($rowDates->length > 0)
            {
                $rowWeekDays     = $finder->query(".//th[contains(@class, 'dnevnik_day')]", $row);
                $currentDate1    = $rowDates->item(0)->textContent;
                $currentWeekday1 = $rowWeekDays->item(0)->textContent;
                $currentDate2    = $rowDates->item(1)->textContent;
                $currentWeekday2 = $rowWeekDays->item(1)->textContent;
                continue;
            }

            $tds = $finder->query(".//td", $row);

            foreach ($tds as $c => $td)
            {
                if (!isset($tdMap[$c]))
                {
                    continue;
                }
                $val = @trim($td->firstChild->textContent, " \r\n.");
                if ($tdMap[$c] == 'marks')
                {
                    $val     = array_filter(array_map('trim', explode('/', $val)));
                    $markDiv = $finder->query(".//div", $td);
                    if ($markDiv->length > 0)
                    {
                        foreach ($markDiv->item(0)->childNodes as $mark)
                        {
                            if (!empty($mark->textContent))
                            {
                                $m = trim($mark->textContent, " \r\n");
                                if (!empty($m))
                                {
                                    $val[] = $m;
                                }
                            }
                        }
                    }
                }
                $results[$c > 3 ? $currentDate2 : $currentDate1][$r - 1][$tdMap[$c]] = $val;
            }
        }

        return $results;
    }

    /**
     * @param string $doc
     *
     * @return domDocument
     */
    private static function prepareDoc($doc)
    {
        $dom = new domDocument('1.0', 'utf-8');

        // DomDocument utf hack
        $doc = preg_replace('/<meta.*charset.*>/i', '', $doc);

        // add proper encoding tag
        $doc =
            str_ireplace(
                '<head>',
                '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />',
                $doc
            );

        $dom->loadHTML($doc);
        $dom->preserveWhiteSpace = false;

        return $dom;
    }
}
