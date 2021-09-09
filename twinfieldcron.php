<?php
require_once 'vendor/autoload.php';
use Money\Currency;
use Money\Money;
use PhpTwinfield\ApiConnectors\TransactionApiConnector;
use PhpTwinfield\Enums\DebitCredit;
use PhpTwinfield\Enums\Destiny;
use PhpTwinfield\Enums\LineType;
use PhpTwinfield\Office;
use PhpTwinfield\PurchaseTransaction;
use PhpTwinfield\PurchaseTransactionLine;

function SendXMLBooking($xmlArray = array()) {
	$connection = new \PhpTwinfield\Secure\WebservicesAuthentication("COM009989", "psvpsv12", "PROEF");
	if (!empty($xmlArray)) {
		$office_code = $xmlArray['transaction']['header']['office'];
		$office = Office::fromCode($office_code);
		$officeApi = new \PhpTwinfield\ApiConnectors\OfficeApiConnector($connection);
		$officeApi->setOffice($office);
		$transactionApiConnector = new \PhpTwinfield\ApiConnectors\TransactionApiConnector($connection);

		$purchaseTransaction = new \PhpTwinfield\PurchaseTransaction($connection);

		foreach ($xmlArray['transaction'] as $key => $xml) {
			//Header start here
			if ($key == '@attributes') {
				if ($xml['destiny'] == 'temporary') {
					$purchaseTransaction
						->setDestiny(Destiny::TEMPORARY());
				}
				$purchaseTransaction->setRaiseWarning($xml['raisewarning']);
			}
			if ($key == 'header') {
				$purchaseTransaction
					->setCode($xml['code'])
					->setCurrency(new Currency($xml['currency']))
					->setDate(new \DateTimeImmutable($xml['date']))
					->setPeriod($xml['period'])
					->setInvoiceNumber($xml['invoicenumber'])
					->setPaymentReference($xml['paymentreference'])
					->setOffice(Office::fromCode($office_code))
					->setDueDate(new \DateTimeImmutable($xml['duedate']));
			}

			//Lines start from here
			if ($key == 'lines') {

				foreach ($xml['line'] as $lkey => $line) {
					if ($line['@attributes']['type'] == 'total') {
						$totalLine = new \PhpTwinfield\PurchaseTransactionLine($connection);
						$totalLine
							->setLineType(LineType::TOTAL())
							->setId($line['@attributes']['id'])
							->setDim1($line['dim1'])
							->setDim2($line['dim2'])
							->setValue(Money::EUR(str_replace(".", "", $line['value'])))
							->setDebitCredit(($line['debitcredit'] == 'debit') ? DebitCredit::DEBIT() : DebitCredit::CREDIT())
							->setDescription('');
					} else if ($line['@attributes']['type'] == 'detail') {
						$detailLine = new \PhpTwinfield\PurchaseTransactionLine($connection);
						$detailLine
							->setLineType(LineType::DETAIL())
							->setId($line['@attributes']['id'])
							->setDim1($line['dim1'])
							->setValue(Money::EUR(str_replace(".", "", $line['value'])))
							->setDebitCredit(($line['debitcredit'] == 'debit') ? DebitCredit::DEBIT() : DebitCredit::CREDIT())
							->setDescription($line['description'])
							->setVatCode($line['vatcode']);
					}
				}
			}
		}
		$purchaseTransaction
			->addLine($totalLine)
			->addLine($detailLine);
		return $transactionApiConnector->send($purchaseTransaction);
	}

}

// Section from where reading the XML files from directory start here

$files = glob("uploads/*xml");
if (is_array($files)) {
	$succss_cnt = 0;
	foreach ($files as $filename) {
		$xmlfile = file_get_contents($filename);
		// Convert xml string into an object
		$xmlString = simplexml_load_string($xmlfile);

// Convert into json
		$xml_to_json = json_encode($xmlString);

// Convert into associative array
		$xmlArray = json_decode($xml_to_json, true);
		$result = SendXMLBooking($xmlArray);
		if ($result) {
			// Deleting the XML file after processed
			unlink($filename);
			$succss_cnt++;
		}
	}
	if ($succss_cnt > 0) {
		echo "<script>alert('" . $succss_cnt . " Booking has been created successfully');</script>";
	}
}
?>
