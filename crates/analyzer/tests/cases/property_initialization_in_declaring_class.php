<?php

declare(strict_types=1);

abstract class FormControl
{
    // @mago-expect analysis:uninitialized-property
    protected string $user;

    // @mago-expect analysis:uninitialized-property
    protected string $storageManager;
}

final class NoteForm extends FormControl
{
    public function __construct(
        private string $note,
    ) {
    }

    public function note(): string
    {
        return $this->note . $this->user . $this->storageManager;
    }
}

final class FeeForm extends FormControl
{
    public function __construct(
        private int $fee,
    ) {
    }

    public function fee(): int
    {
        return $this->fee;
    }
}

abstract class Ledger
{
    // @mago-expect analysis:uninitialized-property
    protected array $conversionRates;

    public function __construct()
    {
    }
}

final class InvoiceLedger extends Ledger
{
    public function __construct()
    {
        parent::__construct();
        $this->conversionRates = [];
    }
}

class Compiler
{
    private string $outputDir;

    public function __construct(string $outputDir)
    {
        $this->setOutputDir($outputDir);
    }

    public function setOutputDir(string $outputDir): void
    {
        $this->outputDir = $outputDir;
    }

    public function outputDir(): string
    {
        return $this->outputDir;
    }
}

final class PriceTier
{
    private string $currency;

    public function __construct(bool $legacy)
    {
        if ($legacy) {
            $this->initLegacy();
        } else {
            $this->initCurrent();
        }
    }

    public function currency(): string
    {
        return $this->currency;
    }

    private function initLegacy(): void
    {
        $this->currency = 'USD';
    }

    private function initCurrent(): void
    {
        $this->currency = 'EUR';
    }
}

trait BlobUpload
{
    private string $blobUploadRoot;

    // @mago-expect analysis:uninitialized-property
    private string $blobUploadOwner;

    protected function initializeBlobUpload(string $root): void
    {
        $this->blobUploadRoot = $root;
    }

    public function blobUpload(): string
    {
        return $this->blobUploadRoot . $this->blobUploadOwner;
    }
}

final class AvatarControl
{
    use BlobUpload;

    public function __construct()
    {
        $this->initializeBlobUpload('/tmp');
    }
}

class Repository
{
    public function __construct(
        protected string $table,
    ) {
    }
}

final class ItemRepository extends Repository
{
    // @mago-expect analysis:uninitialized-property
    private string $alias;

    public function alias(): string
    {
        return $this->alias . $this->table;
    }
}
