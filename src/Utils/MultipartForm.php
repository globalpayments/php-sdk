<?php

namespace GlobalPayments\Api\Utils;

class MultipartForm
{
    // content property
    private $content;

    public function __construct(bool $appendJsonFlag = true)
    {
        $this->content = [];

        if ($appendJsonFlag) {
            $this->content['1'] = 'json';
        }
    }

    public function content()
    {
        return $this->content;
    }

    public function toRequest()
    {
        $boundary = '--GlobalPaymentsSDK';
        return $this->buildDataFiles($boundary, $this->content);
    }

    public function set(string $key, $value, bool $force = false)
    {
        if (empty($this->content[$key]) || $force) {
            $this->content[$key] = $value;
        }
        return $this;
    }

    public function toJson()
    {
        return json_encode(array('fieldValues' => $this->content));
    }

    // https://gist.github.com/maxivak/18fcac476a2f4ea02e5f80b303811d5f
    private function buildDataFiles($boundary, $fields /*, $files*/)
    {
        $data = '';
        $eol = "\r\n";
    
        $delimiter = '' . $boundary;
    
        foreach ($fields as $name => $content) {
            if (strpos($name, 'Prefix')) {
                continue;
            }
            $data .= "--" . $delimiter . $eol
                . 'Content-Type: text/plain; charset=utf-8'. $eol
                . 'Content-Disposition: form-data; name=' . $name . '' .$eol.$eol
                . $content . $eol;
        }
    
        // We're not uploading files right now, but possibly in the future
        /*foreach ($files as $name => $content) {
            $data .= "--" . $delimiter . $eol
                . 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $name . '"' . $eol
                //. 'Content-Type: image/png'.$eol
                . 'Content-Transfer-Encoding: binary'.$eol
                ;

            $data .= $eol;
            $data .= $content . $eol;
        }*/
        $data .= "--" . $delimiter . "--".$eol;
    
    
        return $data;
    }
}
