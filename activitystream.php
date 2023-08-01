<?php

class ActivityStream
{
    private $rowType = 0;
    private $nomUnite = '';
    private $activityTypes = [];
    private $dates = [];
    private $heures = [];
    private $volumesHoraires = [];
    private $descriptionsActivites = [];
    private $animateurs = [];
    private $jeunes = [];
    private $nbActivites = 0;

    function __construct($nomUnite) {
        $this->nomUnite = $nomUnite;
        $this->rowType = 'Activités';
    }

    function addRow($row)
    {
        $firstCell = reset($row);
        switch ($firstCell) {
            case "Animateurs":
                $this->rowType = "Animateurs rows";
                return;
            case "Jeunes":
                $this->rowType = "Jeunes rows";
                return;

            case "Total animateurs par activité":
            case "Nombre d’heures animateurs":
            case "Nombre de jours  animateurs":
            case "Total jeunes par activité":
            case "Nombre d’heures jeunes":
            case "Nombre de jours  jeunes":
                $this->rowType = "";
                return;
        }

        $this->nbActivites = max($this->nbActivites, count($row));

        switch ($this->rowType) {
            case 'Activités':
                $this->activityTypes = $row;
                $this->rowType = 'Date de début et de fin';
                return;

            case 'Date de début et de fin':
                $this->dates = $row;
                $this->rowType = 'Heure de début et de fin';
                return;

            case 'Heure de début et de fin':
                $this->heures = $row;
                $this->rowType = 'Volume horaire forfaitaire';
                return;

            case 'Volume horaire forfaitaire':
                $this->volumesHoraires = $row;
                $this->rowType = "Description de l'activité";
                return;

            case "Description de l'activité":
                $this->descriptionsActivites = $row;
                return;

            case "Animateurs rows":
                $this->animateurs[] = $row;
                return;

            case "Jeunes rows":
                $this->jeunes[] = $row;
                return;

            case "":
                return;

            default:
                throw new Exception("Error Processing Request", 1);
        }
    }

    public function finalize()
    {
        $ret = [];

        for ($k = 2; $k <= $this->nbActivites; $k++)
        {

            foreach ($this->animateurs as $activiteAnimateur)
            {
                $presence = $activiteAnimateur[$k] ?? '';

                if ($presence == 'X')
                {
                    if ($presence == 'X')
                    {
                        $ret[] = $this->getRow($activiteAnimateur, $k, 'Animateur');
                    }
                }
            }

            foreach ($this->jeunes as $activiteAnimateur)
            {
                $presence = $activiteAnimateur[$k] ?? '';

                if ($presence == 'X')
                {
                    $ret[] = $this->getRow($activiteAnimateur, $k, 'Jeune');
                }
            }

        }

        return $ret;
    }

    private function getRow($personne, $k, $typePersonne = 'Jeune')
    {
        $activityType = $this->activityTypes[$k] ?? '';
        $dates = $this->dates[$k] ?? '';
        $heures = $this->heures[$k] ?? '';
        $volumesHoraires = $this->volumesHoraires[$k] ?? '';
        $descriptionsActivites = $this->descriptionsActivites[$k] ?? '';

        $nom = $personne[0];
        $regime = $personne[1] ?? '';

        // $dates
        // Samedi 05/12/2020
        // Samedi 05/12/2020
        // Samedi 05/12/2020
        // Samedi 05/12/2020
        // Samedi 05/12/2020
        // Samedi 12/12/2020 - Dimanche 13/12/2020
        // Samedi 12/12/2020 - Dimanche 13/12/2020
        // Samedi 12/12/2020 - Dimanche 13/12/2020
        // Samedi 12/12/2020 - Dimanche 13/12/2020
        // Samedi 12/12/2020 - Dimanche 13/12/2020

        return [$this->nomUnite, $nom, $regime, $typePersonne, $activityType, $dates, $heures, $volumesHoraires, $descriptionsActivites];
    }
}