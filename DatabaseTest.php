<?php

namespace FpDbTest;

use Exception;
use FpDbTest\QueryTemplate\InvalidTemplateSyntaxException;

class DatabaseTest
{
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    public function testBuildQuery(): void
    {
        $results = [];

        $results[] = $this->db->buildQuery('SELECT name FROM users WHERE user_id = 1');

        $results[] = $this->db->buildQuery(
            'SELECT * FROM users WHERE name = ? AND block = 0',
            ['Jack']
        );

        $results[] = $this->db->buildQuery(
            'SELECT ?# FROM users WHERE user_id = ?d AND block = ?d',
            [['name', 'email'], 2, true]
        );

        $results[] = $this->db->buildQuery(
            'UPDATE users SET ?a WHERE user_id = -1',
            [['name' => 'Jack', 'email' => null]]
        );

        foreach ([null, true] as $block) {
            $results[] = $this->db->buildQuery(
                'SELECT name FROM users WHERE ?# IN (?a){ AND block = ?d}',
                ['user_id', [1, 2, 3], $block ?? $this->db->skip()]
            );
        }

        $results[] = $this->db->buildQuery('SELECT name FROM users WHERE user_id = ?1', [10]);
        $results[] = $this->db->buildQuery(
            'SELECT name FROM users WHERE user_id = ? AND ?>balance',
            [10, 0.0000001]
        );
        $results[] = $this->db->buildQuery(
            'SELECT name FROM users WHERE name = ? AND comment = ?',
            ["O'Neil", '"{Quoted text}"']
        );

        $correct = [
            'SELECT name FROM users WHERE user_id = 1',
            'SELECT * FROM users WHERE name = \'Jack\' AND block = 0',
            'SELECT `name`, `email` FROM users WHERE user_id = 2 AND block = 1',
            'UPDATE users SET `name` = \'Jack\', `email` = NULL WHERE user_id = -1',
            'SELECT name FROM users WHERE `user_id` IN (1, 2, 3)',
            'SELECT name FROM users WHERE `user_id` IN (1, 2, 3) AND block = 1',

            'SELECT name FROM users WHERE user_id = 101',
            sprintf('SELECT name FROM users WHERE user_id = 10 AND %s>balance', 0.0000001),
            'SELECT name FROM users WHERE name = \'O\\\'Neil\' AND comment = \'"{Quoted text}"\'',

        ];


        if ($results !== $correct) {
            print_r($results);
            print_r($correct);
            throw new Exception('Failure.');
        }

        try {
            $this->db->buildQuery('SELECT * FROM users WHERE name = ? { AND block = 0');
        } catch (InvalidTemplateSyntaxException $x) {
            echo sprintf('TEST OK: `%s` exception caught when passed unclosed condition' . PHP_EOL, $x->getMessage());
        }

        try {
            $this->db->buildQuery('SELECT * FROM users WHERE name = ?  AND block = 0}');
        } catch (InvalidTemplateSyntaxException $x) {
            echo sprintf('TEST OK: `%s` exception caught when passed unexpected condition close bracket' . PHP_EOL, $x->getMessage());
        }

        try {
            $this->db->buildQuery('SELECT * FROM users WHERE name = ?  AND block = 0', [$this->db->skip()]);
        } catch (InvalidTemplateSyntaxException $x) {
            echo sprintf('TEST OK: `%s` exception caught when passed skip outside of condition' . PHP_EOL, $x->getMessage());
        }

        try {
            $this->db->buildQuery('SELECT * FROM users WHERE name = ? AND status = ?', ['Bob']);
        } catch (InvalidTemplateSyntaxException $x) {
            echo sprintf('TEST OK: `%s` exception caught when passed placeholder without value' . PHP_EOL, $x->getMessage());
        }

        try {
            $this->db->buildQuery('SELECT * FROM users WHERE name = ?', [['Bob']]);
        } catch (\InvalidArgumentException $x) {
            echo sprintf('TEST OK: `%s` exception caught when passed array to scalar placeholder' . PHP_EOL, $x->getMessage());
        }

        try {
            $this->db->buildQuery('SELECT * FROM users WHERE name = ?a', ['Bob']);
        } catch (\InvalidArgumentException $x) {
            sprintf('TEST OK: `%s` exception caught when passed string to array placeholder' . PHP_EOL, $x->getMessage());
        }

        try {
            echo $this->db->buildQuery('SELECT * FROM users WHERE name = ?a', [[['Bob', 'Alice']]]);
        } catch (\InvalidArgumentException $x) {
            sprintf('TEST OK: `%s` exception caught when passed array as an element for array placeholder' . PHP_EOL, $x->getMessage());
        }

        try {
            $this->db->buildQuery('SELECT * FROM users WHERE name = ?d', ['z']);
        } catch (\InvalidArgumentException $x) {
            echo sprintf('TEST OK: `%s` exception caught when passed string to int placeholder' . PHP_EOL, $x->getMessage());
        }

        try {
            $this->db->buildQuery('SELECT * FROM users WHERE name = ?f', ['z']);
        } catch (\InvalidArgumentException $x) {
            echo sprintf('TEST OK: `%s` exception caught when passed string to float placeholder' . PHP_EOL, $x->getMessage());
        }
    }
}
