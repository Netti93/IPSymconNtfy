<?php

declare(strict_types=1);
	class Ntfy extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();

            $this->RegisterPropertyString('URL', 'https://ntfy.sh');
            $this->RegisterPropertyBoolean('USEAUTH', false);
            $this->RegisterPropertyBoolean('USETOKEN', false);
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

		public function GetConfigurationForm()
		{
			$useauth = $this->ReadPropertyBoolean('USEAUTH');
			$usetoken = $this->ReadPropertyBoolean('USETOKEN');

			$form['elements'] = [
				[
					"type" => "ValidationTextBox",
					"name" => "URL",
					"caption" => "Server URL (required)"
				],
				[
					"type" => "CheckBox",
					"name" => "USEAUTH",
					"caption" => "Use authentication",
					"onChange" => 'NTFY_UseAuthentication($id, $USEAUTH);'
				],
				[
					"type" => "ExpansionPanel",
					"name" => "AUTHPANEL",
					"caption" => "Authentication",
					"visible" => $useauth,
					"items" => [
						[
							"type" => "CheckBox",
							"name" => "USETOKEN",
							"caption" => "Use Token instead of credentials",
							"onChange" => 'NTFY_ToggleUseToken($id, $USETOKEN);'
						],
						[
							"type" => "PasswordTextBox",
							"name" => "TOKEN",
							"caption" => "Application Token (required)",
							"visible" => $usetoken
						],
						[
							"type" => "ValidationTextBox",
							"name" => "USERNAME",
							"caption" => "Username (required)",
							"visible" => !$usetoken
						],
						[
							"type" => "PasswordTextBox",
							"name" => "PASSWORD",
							"caption" => "Password (required)",
							"visible" => !$usetoken
						]
					]
				]
			];

			$form['actions'] = [
				[
					"type" => "ValidationTextBox",
					"name" => "TOPIC",
					"caption" => "Topic"
				],
				[
					"type" => "Button",
					"caption" => "Send test message",
					"onClick" => 'if (NTFY_SendTestMessage($id, $TOPIC)) echo "OK"; else echo "Error";'
				]
			];

			$form['status'] = [
				[
					"code" => "102",
					"icon" => "active",
					"caption" => "OK"
				],
				[
					"code" => "201",
					"icon" => "error",
					"caption" => "An error occurred - please check the log"
				],
				[
					"code" => "202",
					"icon" => "error",
					"caption" => "Invalid URL"
				],
				[
					"code" => "203",
					"icon" => "error",
					"caption" => "Unauthorized"
				],
				[
					"code" => "204",
					"icon" => "error",
					"caption" => "Forbidden"
				]
			];

			return json_encode($form);
		}

		public function UseAuthentication(bool $status)
		{
			$this->UpdateFormField("AUTHPANEL", "visible", $status);
			$this->UpdateFormField("AUTHPANEL", "expanded", $status);
		}

		public function ToggleUseToken(bool $status)
		{
			$this->UpdateFormField("TOKEN", "visible", $status);
			$this->UpdateFormField("USERNAME", "visible", !$status);
			$this->UpdateFormField("PASSWORD", "visible", !$status);
		}

        private function BuildMessageURL(string $topic)
        {
            return rtrim($this->ReadPropertyString('URL'), '/').'/'.$topic;
        }

        public function SendTestMessage(string $topic)
        {
            return $this->SendMessageWithExtras($topic, $this->Translate('This is a test message from your Symcon instance'));
        }

        public function SendMessageWithExtras(string $topic, string $message, string $title = "", int $priority = 0, array $extras = [])
        {
			$headers = ['Content-Type: text/plain'];

            curl_setopt_array($ch = curl_init(), [
                CURLOPT_URL        => $this->BuildMessageURL($topic),
                CURLOPT_POSTFIELDS => $message,
                CURLOPT_SAFE_UPLOAD    => true,
                CURLOPT_RETURNTRANSFER => true,
            ]);

			if($this->ReadPropertyBoolean('USEAUTH'))
			{
				if($this->ReadPropertyBoolean('USETOKEN'))
				{
					$headers[] = 'Authorization: Bearer '.$this->ReadPropertyString('TOKEN');
				} 
				else 
				{
					curl_setopt($ch, CURLOPT_USERPWD, $this->ReadPropertyString('USERNAME').':'.$this->ReadPropertyString('PASSWORD'));
				}
			}

			// TODO: set Headers for fields in $extras
			//curl_setopt($ch, CURLOPT_HTTPHEADER[], 'Title: Dies ist ein Titel');

			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$response = curl_exec($ch);

			print_r($response);

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