<?php


namespace App\Entities;


use InvalidArgumentException;

class UserInfo implements \JsonSerializable
{
    const RECENT_TEST_NONE = 'none';
    const RECENT_TEST_POSITIVE = 'positive';
    const RECENT_TEST_NEGATIVE = 'negative';

    /**
     * @var string $id
     */
    private $id;
    /**
     * @var string $recentTest
     */
    private $recentTest;

    public function __construct(string $id, string $recentTest)
    {
        $this->validateRecentTest($recentTest);

        $this->id = $id;
        $this->recentTest = $recentTest;
    }

    /**
     * @param string $recentTest
     * @throws InvalidArgumentException
     */
    private function validateRecentTest(string $recentTest): void
    {
        if (!in_array($recentTest, [self::RECENT_TEST_NONE, self::RECENT_TEST_POSITIVE, self::RECENT_TEST_NEGATIVE])) {
            throw new InvalidArgumentException("Recent test value is not valid");
        };
    }

    public static function createNew(string $recentTest): UserInfo
    {
        return new UserInfo(uniqid(), $recentTest);
    }

    /**
     * @return string
     */
    public function getRecentTest(): string
    {
        return $this->recentTest;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            "id" => $this->id,
            "recentTest" => $this->recentTest
        ];
    }
}