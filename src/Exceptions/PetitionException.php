<?php namespace Api\Exceptions;

class PetitionException extends \RuntimeException implements PetitionInterface
{
    public static function centerSimilar( array $centers = [] )
    {
        return new self( lang( 'Center.centerSimilar', array( 'centers' => implode( ',', $centers ) ) ) );
    }
}