<?php

declare(strict_types=1);

namespace App\Bot;

use BotMan\BotMan\BotMan;
use Str;
use Webmozart\Assert\Assert;

abstract class Command
{
    protected const REST_OF_THE_QUERY = 'rest_of_the_query';

    abstract public static function getCommand(): string;

    abstract public static function getCommandPattern(): string;

    abstract public static function getDesc(): string;

    /**
     * @return array<string>
     */
    abstract protected function getParamList(): array;

    /**
     * @param BotMan $bot
     * @return array<string,string>
     */
    final protected function getParams(BotMan $bot): array
    {
        return $this->parseParams($this->getParamString($bot), $this->getParamList());
    }

    /**
     * @param string $paramString
     * @param array<string> $paramList
     * @return array<string,string>
     * @SuppressWarnings("UndefinedVariable")
     */
    private function parseParams(string $paramString, array $paramList): array
    {
        $params = [];

        unset($paramList[self::REST_OF_THE_QUERY]);

        foreach ($paramList as $name) {
            $pattern = "/(^|\s){$name}=([^\s$]*)($|\s)/";

            $matches = [];

            if (!preg_match($pattern, $paramString, $matches)) {
                continue;
            }

            $params[$name] = empty(trim($matches[2])) ? null : trim($matches[2]);

            $paramString = (string)preg_replace($pattern, ' ', $paramString);
        }

        $params[self::REST_OF_THE_QUERY] = empty(trim($paramString)) ? null : trim($paramString);

        return array_filter($params, static fn ($value) => $value !== null);
    }

    private function getParamString(BotMan $bot): string
    {
        Assert::startsWith($bot->getMessage()->getText(), static::getCommand());

        return Str::replaceFirst(static::getCommand() . ' ', '', $bot->getMessage()->getText());
    }
}
