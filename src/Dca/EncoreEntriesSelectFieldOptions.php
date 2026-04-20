<?php

namespace HeimrichHannot\EncoreBundle\Dca;

class EncoreEntriesSelectFieldOptions
{
    protected string $fieldName = EncoreEntriesSelectField::NAME_DEFAULT;
    protected bool $includeActiveCheckbox = false;
    protected ?array $fieldLabel = null;
    protected ?array $selectLabel = null;
    protected ?array $checkboxLabel = null;

    public function __construct(
        protected string $table,
    ) {
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): void
    {
        $this->fieldName = $fieldName;
    }

    public function isIncludeActiveCheckbox(): bool
    {
        return $this->includeActiveCheckbox;
    }

    public function setIncludeActiveCheckbox(bool $includeActiveCheckbox): void
    {
        $this->includeActiveCheckbox = $includeActiveCheckbox;
    }

    public function getFieldLabel(): ?array
    {
        return $this->fieldLabel;
    }

    public function setFieldLabel(?array $fieldLabel): void
    {
        $this->fieldLabel = $fieldLabel;
    }

    public function getSelectLabel(): ?array
    {
        return $this->selectLabel;
    }

    public function setSelectLabel(?array $selectLabel): void
    {
        $this->selectLabel = $selectLabel;
    }

    public function getCheckboxLabel(): ?array
    {
        return $this->checkboxLabel;
    }

    public function setCheckboxLabel(?array $checkboxLabel): void
    {
        $this->checkboxLabel = $checkboxLabel;
    }
}
