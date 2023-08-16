<?php

declare(strict_types=1);
	class Ntfy extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();

            $this->RegisterPropertyString('URL', '');
            $this->RegisterPropertyBoolean('USE_TOKEN', false);
            $this->RegisterPropertyString('TOKEN', '');
            $this->RegisterPropertyString('USERNAME', '');
            $this->RegisterPropertyString('PASSWORD', '');
		}

		public function Destroy()
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
		}

		public function ToggleUseToken(bool $status)
		{
			echo $status;
			$this->UpdateFormField("TOKEN", "visible", $status);
			$this->UpdateFormField("USERNAME", "visible", !$status);
			$this->UpdateFormField("PASSWORD", "visible", !$status);
		}

        private function BuildMessageURL(string $topic)
        {
            return rtrim($this->ReadPropertyString('URL'), '/').'/'.$topic;
        }

        public function SendTestMessage()
        {
            return $this->SendMessageWithExtras($this->Translate('Test message'), $this->Translate('This is a test message from your Symcon instance'));
        }

        public function SendMessageWithExtras(string $topic, string $message, string $title = "", int $priority = 0, array $extras = [])
        {
            /*
			if(!empty($extras))
            {
                $postfields['extras'] = $extras;
            }
			*/

            curl_setopt_array($ch = curl_init(), [
                CURLOPT_URL        => $this->BuildMessageURL(),
                CURLOPT_HTTPHEADER => ['Content-Type: text/plain'],
                CURLOPT_POST       => true,
                CURLOPT_POSTFIELDS => $message,
                CURLOPT_SAFE_UPLOAD    => true,
                CURLOPT_RETURNTRANSFER => true,
            ]);

			// TODO: set Headers for fields in $extras
			//curl_setopt($ch, CURLOPT_HTTPHEADER[], 'Title: Dies ist ein Titel');

            $response = curl_exec($ch);

            // Check for errors and display the error message
            if (!$response) {
                $errorArr = [
                    'error'            => curl_strerror(curl_errno($ch)),
                    'errorCode'        => curl_getinfo($ch, CURLINFO_RESPONSE_CODE),
                    'errorDescription' => curl_error($ch),
                ];
                $this->LogMessage(json_encode($errorArr), KL_ERROR);
                $this->SetStatus(201);

                return false;
            }

            curl_close($ch);

            $responseObject = json_decode($response);
            if (property_exists($responseObject, 'appid')) {
                $this->SetStatus(102);

                return true;
            } elseif (property_exists($responseObject, 'errorCode') && $responseObject->{'errorCode'} == 404) {
                $this->SetStatus(202);
            } elseif (property_exists($responseObject, 'errorCode') && $responseObject->{'errorCode'} == 401) {
                $this->SetStatus(203);
            } elseif (property_exists($responseObject, 'errorCode') && $responseObject->{'errorCode'} == 403) {
                $this->SetStatus(204);
            }

            $this->LogMessage($response, KL_ERROR);

            return false;
        }
	}