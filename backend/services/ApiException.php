<?php
// Thrown by services when a request cannot be completed. The error handler in index.php
// turns it into a JSON response with this HTTP status code (400, 404, 409, ...).

class ApiException extends Exception
{
    public function __construct(string $message, int $status = 400)
    {
        parent::__construct($message, $status);
    }

    public function getStatus(): int
    {
        return $this->getCode();
    }
}
