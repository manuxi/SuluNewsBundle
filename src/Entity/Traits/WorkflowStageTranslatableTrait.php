<?php

namespace Manuxi\SuluNewsBundle\Entity\Traits;

trait WorkflowStageTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    public function getWorkflowStage(): ?int
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }
        return $translation->getWorkflowStage();
    }

    public function setWorkflowStage(int $WorkflowState): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }
        $translation->setWorkflowStage($WorkflowState);
        return $this;
    }

}
