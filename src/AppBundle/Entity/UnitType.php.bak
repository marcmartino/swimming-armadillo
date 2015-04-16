<?php
namespace AppBundle\Entity;


use AppBundle\Exception\UnitTypeNotFoundException;

class UnitType extends AbstractEntity {

    /**
     * @param $slug
     * @return array
     */
    public function getUnitType($slug)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM unit_types WHERE slug = :slug
        ");
        $stmt->execute([':slug' => $slug]);
        if ($stmt->rowCount() == 0) {
            throw new UnitTypeNotFoundException("Measurement type '$slug' not found");
        }
        return $stmt->fetch();
    }

}