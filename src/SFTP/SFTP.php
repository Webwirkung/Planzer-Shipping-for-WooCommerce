<?php

namespace Planzer\SFTP;

use phpseclib3\Net\SFTP as SFTPLib;
use Planzer\Package\Package;

class SFTP
{
  private SFTPLib $sftp;
  private const PORT = 22;

  public function __construct()
  {
    try {
      $this->sftp = new SFTPLib($this->getHost(), $this->getPort());
      if (! $this->sftp->login($this->getLogin(), $this->getPassword())) {
        throw new \Exception("PLANZER SFTP ERROR: Could not log into FTP: {$this->getHost()}", 1);
      }
    } catch (\Exception $e) {
      error_log('Planzer: ERROR connecting to Planzer SFTP server failed');
      throw new \Exception(__('There was a problem with the connection to the server, please try again later or contact administrator.', 'planzer'));
    }
  }

  public function __destruct()
  {
    $this->sftp->disconnect();
  }

  private function getPort(): int
  {
    return self::PORT;
  }

  private function getLogin(): string
  {
    return 'WooCommerce';
  }

  private function getPassword(): string
  {
    return 'pLa27@WebWoComm09!';
  }

  private function getHost(): string
  {
    return get_option('planzer_ftp_live_server_address', 'lobplalb02.planzer.ch');
  }

  public function upload(string $csv, Package $package): void
  {
    if ($this->sftp->isConnected()) {
      $upload = $this->sftp->put(sprintf("Eingang/PAKET_%s_%s_%s_WP.csv", $package->getPackageNumber(), time(), rand()), $csv);

      if (false === $upload) {
        $errors = $this->sftp->getSFTPErrors();
        $errors[0] ??= 'undefined FTP error :-(';

        $this->sftp->disconnect();
        throw new \Exception("PLANZER SFTP ERROR: {$errors[0]}");
      }

      $this->sftp->disconnect();
    }
  }
}
