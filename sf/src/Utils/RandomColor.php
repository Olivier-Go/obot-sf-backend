<?php
/**
 * https://mdigi.tools/random-material-color/
 */
namespace App\Utils;

abstract class RandomColor
{
    private const FAMILIES = [
        'blue',
        'green',
        'purple',
        'orange',
        'yellow',
        'red',
        'grey',
    ];

    private const COLORS = [
        'blue1' => 'rgb(48, 79, 254)',
        'green1' => 'rgb(0, 105, 92)',
        'purple1' => 'rgb(142, 36, 170)',
        'orange1' => 'rgb(255, 167, 38)',
        'yellow1' => 'rgb(255, 209, 128)',
        'blue2' => 'rgb(128, 222, 234)',
        'green2' => 'rgb(0, 230, 118)',
        'purple2' => 'rgb(94, 53, 177)',
        'orange2' => 'rgb(255, 110, 64)',
        'red1' => 'rgb(239, 83, 80)',
        'blue3' => 'rgb(129, 212, 250)',
        'green3' => 'rgb(100, 221, 23)',
        'grey1' => 'rgb(176, 190, 197)',
        'red2' => 'rgb(255, 138, 128)',
        'blue4' => 'rgb(0, 229, 255)',
        'red3' => 'rgb(229, 57, 53)',
        'green4' => 'rgb(0, 200, 83)',
        'yellow2' => 'rgb(255, 196, 0)',
        'green5' => 'rgb(100, 221, 23)',
        'grey2' => 'rgb(120, 144, 156)'
    ];

    static public function randomOne(): string
    {
        $randKey = array_rand(self::COLORS, 1);
        return self::COLORS[$randKey];
    }

    static public function randomPair(int $pair = 1): array
    {
        $max = min($pair, 2);
        $families = self::FAMILIES;
        shuffle($families);
        $colors = [];

        foreach ($families as $family) {
            $i = 1;
            foreach (self::COLORS as $familyGroup => $color) {
                if ((substr($familyGroup,0, -1) === $family) && $i <= $max) {
                    $colors[] = $color;
                    $i++;
                }
            }
        }

        return $colors;
    }

    static public function all(): array
    {
        $colors = [];
        foreach (self::COLORS as $color) {
            $colors[] = $color;
        }
        return $colors;
    }
}