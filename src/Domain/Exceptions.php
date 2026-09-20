<?php

declare(strict_types=1);

namespace Citas\Domain;

use RuntimeException;

final class CitaConflictException extends RuntimeException
{
}

final class CitaNotFoundException extends RuntimeException
{
}
