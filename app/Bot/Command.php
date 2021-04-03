<?php

declare(strict_types=1);

namespace App\Bot;

use BotMan\BotMan\BotMan;
use InvalidArgumentException;
use Str;
use Webmozart\Assert\Assert;

abstract class Command
{
    protected const REST_OF_THE_QUERY = 'rest_of_the_query';

    abstract public static function getCommand(): string;

    abstract public static function getCommandPattern(): string;

    /**
     * @return array<string,array<string>>
     */
    abstract protected function getParamList(): array;

    /**
     * @param non-empty-array<string,null|string> $params
     * @return non-empty-array<string,mixed>
     */
    abstract protected function mapParams(array $params): array;

    /**
     * @param BotMan $bot
     * @return non-empty-array<string,mixed>
     */
    final protected function getParams(BotMan $bot): array
    {
        return $this->mapParams($this->parseParams($this->getParamString($bot), $this->getParamList()));
    }

    /**
     * @param string $paramString
     * @param array<string,array<string>> $paramList
     * @return non-empty-array<string,null|string>
     * @SuppressWarnings("UndefinedVariable")
     */
    final private function parseParams(string $paramString, array $paramList): array
    {
        $params = [];

        foreach ($paramList as $name => $rules) {
            $pattern = "/{$name}=([^ ])/";

            $matches = [];

            if (preg_match($pattern, $paramString, $matches)) {
                $params[$name] = $matches[1];

                preg_replace($pattern, '', $paramString);

                continue;
            }

            if (in_array('required', $rules)) {
                throw new InvalidArgumentException("absent {$name}");
            }
        }

        $params[self::REST_OF_THE_QUERY] = empty($paramString) ? null : $paramString;

        return $params;
    }

    final private function getParamString(BotMan $bot): string
    {
        Assert::startsWith($bot->getMessage()->getText(), static::getCommand() . ' ');

        return Str::replaceFirst(static::getCommand() . ' ', '', $bot->getMessage()->getText());
    }
}
