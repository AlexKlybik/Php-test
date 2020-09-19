<?php

class TableData
{
    private $constParams = [
        'page',
        'limit'
    ];

    /**
     * @return string[]
     */
    public function getConstParams(): array
    {
        return $this->constParams;
    }

    public function actionGet()
    {
        http_response_code(200);
        if (!empty($_GET['page'] && !empty($_GET['limit']))) {
            try {
                $filterAttributes = $this->preparationFilterAttributes($_GET);

                $tableDataQuery = $this->dataPreparationQueryForGetData((int)$_GET['page'], (int)$_GET['limit'], $filterAttributes);
                $tableData = Db::setResultQuery($tableDataQuery);

                if (!empty($tableData)) {
                    $data = [
                        "head" => array_keys($tableData[0]),
                        "body" => array_map(function ($row) {
                            return array_values($row);
                        }, $tableData)
                    ];
                    return json_encode(Response::format("1", "", $data));
                } else {
                    http_response_code(404);
                    return json_encode(Response::format("0", "Data not found"));
                }
            } catch (Throwable $e) {
                //necessary to log an error
                throw $e;
            }
        } else {
            http_response_code(400);
            return json_encode(Response::format("0", "Incorrect parameters. Must pass 'page' and 'limit'"));
        }

    }

    private function dataPreparationQueryForGetData($page, $limit, array $filterAttributes = [])
    {
        $query = "SELECT * FROM test.`test`";
        if (!empty($filterAttributes)) {
            $query .= " WHERE ";
            $conditions = [];
            foreach ($filterAttributes as $key => $filterAttribute) {
                $escapeAttribute = Db::connection()->real_escape_string($filterAttribute);
                $conditions[] = "`$key` = '$escapeAttribute'";
            }
            $query .= implode(' AND ', $conditions);
        }
        $query .= " LIMIT ?,?";

        if ($stmt = Db::connection()->prepare($query)) {
            $offset = ($page - 1) * $limit;
            $stmt->bind_param('ii', $offset, $limit);
            return $stmt;
        } else {
            throw new Exception('Error prepare query ' . Db::connection()->error);
        }
    }

    private function preparationFilterAttributes(array $forPreparations)
    {
        $filterAttributes = [];

        foreach ($forPreparations as $nameAttribute => $attribute) {
            if (!in_array($nameAttribute, $this->getConstParams())) {
                $filterAttributes[$nameAttribute] = $attribute;
            }
        }
        return $filterAttributes;
    }

}