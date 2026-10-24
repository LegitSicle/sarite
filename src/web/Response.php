<?php
namespace sarite\web;

/**
 * Builds the response that is used in `Application.class`. It contains default values that can be changed through the similarly named methods.<br>
 * - $statusCode
 * - $headers
 * - $content
 */
class Response {
    private int $statusCode = 200;
    private array $headers = [];
    private string $content = "";

    /**
     * Class default is `200`. This is will be the value used for `http_response_code()`.
     * @param int $value The new statusCode
     * @return Response
     */
    public function statusCode(int $value): self {
        $this->statusCode = $value;
        return $this;
    }

    /**
     * Adds to the `$headers` array.
     * @param string $name Name of new header
     * @param string $value Value of header
     * @return Response
     */
    public function headers(string $name, string $value): self {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * The HTML content to be displayed.
     * @param string $value A view file's content
     * @return Response
     */
    public function content(string $value): self {
        $this->content = $value;
        return $this;
    }

    /**
     * Shorthand to set a location header.
     * @param string $URI Route to be redirected to
     * @return Response
     */
    public function redirect(string $URI): self {
        $basePath = dirname(dirname($_SERVER['SCRIPT_NAME']));
        $this->headers["Location"] = "{$basePath}{$URI}";
        return $this;
    }

    /**
     * Sends the response code, sets the headers, and echos the view's contents.
     * @return void
     */
    public function send(): void {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        echo $this->content;
    }
}