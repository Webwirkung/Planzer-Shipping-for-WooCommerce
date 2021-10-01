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
      $this->sftp->login($this->getLogin(), $this->getPassword());
    } catch (\Exception $e) {
      error_log('Planzer: ERROR with planzer SFTP server');
      throw new \Exception(__('There was a problem with the connection to the server, please try again later or contact administrator.', 'planzer'));
    }
  }

  private function getPort(): int
  {
    return self::PORT;
  }

  private function getLogin(): string
  {
    return get_option('planzer_ftp_username');
  }

  private function getPassword(): string
  {
    return get_option('planzer_ftp_password');
  }

  private function getHost(): string
  {
    $mode = ('yes' === get_option('planzer_ftp_test_mode')) ? 'test' : 'live';

    return get_option("planzer_ftp_{$mode}_ip_address");
  }

  public function upload(string $csv, Package $package): void
  {
    if ($this->sftp->isConnected()) {
      $upload = $this->sftp->put(sprintf("Eingang/PAKET_%s_%s_WP.csv", $package->getPackageNumber(), time()), $csv);

      if (false === $upload) {
        $errors = $this->sftp->getSFTPErrors();

        throw new \Exception("PLANZER SFTP ERROR: $errors[0]");
      }

      $this->sftp->disconnect();
    }
  }
}
