<?php


class Response
{
    /**
     * @param string $status
     * @param string $error
     * @param array $data need content "head" => [], "body" => [[],[], ...]
     */
    public static function format(string $status = "0", string $error = "", array $data = [])
    {
        $result = (object)[
            "status" => $status,
            "error" => $error
        ];
        if (!empty($data)) {
            if (isset($data['head']) && is_array($data['head']) && isset($data['body']) && is_array($data['body'])) {
                $result->data = (object)$data;
            } else {
                throw new Exception('Not found "head"/"body" in data or "head"/"body" not array');
            }
        }
        return $result;
    }
}