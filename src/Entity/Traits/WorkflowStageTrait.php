<?php

namespace Manuxi\SuluNewsBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait WorkflowStageTrait
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $workflowStage = null;

    public function getWorkflowStage(): ?int
    {
        return $this->workflowStage;
    }

    public function setWorkflowStage($workflowStage): void
    {
        $this->workflowStage = $workflowStage;
    }


}
