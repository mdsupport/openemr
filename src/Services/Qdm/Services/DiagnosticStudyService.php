<?php


namespace OpenEMR\Services\Qdm\Services;


use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\DiagnosticStudyPerformed;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class DiagnosticStudyService extends AbstractQdmService implements QdmServiceInterface
{

    public function getSqlStatement()
    {
        $sql = "SELECT pid, `date`, encounter, code, code_type, ob_value, description, ob_code, ob_type, ob_status
                FROM form_observation
                WHERE ob_type = 'procedure_diagnostic'
                ";
        return $sql;
    }

    public function makeQdmModel(array $record)
    {
        $qdmModel = new DiagnosticStudyPerformed([
            '_pid' => $record['pid'],
            '_encounter' => $record['encounter'],
            'relevantDatetime' => new DateTime([
                'date' => $record['date']
            ]),
            'authorDatetime' => new DateTime([
                'date' => $record['date']
            ]),
            'result' => $this->makeQdmCode($record['ob_code'])
        ]);

        $codes = $this->explodeAndMakeCodeArray($record['code']);
        foreach ($codes as $code) {
            $qdmModel->addCode($code);
        }

        return $qdmModel;
    }
}