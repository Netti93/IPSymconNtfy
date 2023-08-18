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
					"caption" => "Server needs authentication",
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
							"caption" => "Use token instead of credentials",
							"onChange" => 'NTFY_ToggleUseToken($id, $USETOKEN);'
						],
						[
							"type" => "PasswordTextBox",
							"name" => "TOKEN",
							"caption" => "Access token (required)",
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
            return $this->SendMessage($topic, $this->Translate('This is a test message from your Symcon instance'), $this->Translate('Test'));
        }

		public function SendMessage(string $topic, string $message)
		{
			return $this->SendMessage($topic, $message);
		}

		public function SendMessage(string $topic, string $message, string $title)
		{
			return $this->SendMessage($topic, $message, $title);
		}

		public function SendMessage(string $topic, string $message, int $priority)
		{
			return $this->SendMessage($topic, $message, "", $priority);
		}

		public function SendMessage(string $topic, string $message, string $title = "", int $priority = 3)
		{
			$headers = [];

			if($title !== "") {
				$headers[] = "Title: $title";
			}

			$headers[] = "Priority: $priority";

			return $this->SendMessageWithHeaders($topic, $message, $headers);
		}

		public function SendMessageAsJson(string $topic, array $extras = [])
		{
			$headers = ["Content-Type: application/json"];
			
			$extras['topic'] = $topic;

			return $this->SendMessageWithHeaders("", json_encode($extras), $headers);
		}

        public function SendMessageWithHeaders(string $topic, string $message, array $headers = [])
        {
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

			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

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
            if (property_exists($responseObject, 'id')) {
                $this->SetStatus(102);

                return true;
            } elseif (property_exists($responseObject, 'error') && $responseObject->{'code'} == 404) {
                $this->SetStatus(202);
            } elseif (property_exists($responseObject, 'error') && $responseObject->{'code'} == 401) {
                $this->SetStatus(203);
            } elseif (property_exists($responseObject, 'error') && $responseObject->{'code'} == 403) {
                $this->SetStatus(204);
            }

            $this->LogMessage($response, KL_ERROR);

            return false;
        }
	}