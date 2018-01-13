<?php namespace XoopsModules\Newbb;

//adopted from poweradmin (https://github.com/poweradmin)

/**
 * Class IpCheck
 */
class IpCheck
{
    public $ipin;
    public $ipout;
    public $ipver;

    // Return IP type.  4 for IPv4, 6 for IPv6, 0 for bad IP.

    /**
     * @param $ipValue
     */
    public function addressType($ipValue)
    {
        $this->ipin  = $ipValue;
        $this->ipver = 0;

        // IPv4 addresses are easy-peasy
        if (filter_var($this->ipin, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->ipver = 4;
            $this->ipout = $this->ipin;
        }

        // IPv6 is at least a little more complex.
        if (filter_var($this->ipin, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {

            // Look for embedded IPv4 in an embedded IPv6 address, where FFFF is appended.
            if (0 === strpos($this->ipin, '::FFFF:')) {
                $ipv4addr = substr($this->ipin, 7);
                if (filter_var($ipv4addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $this->ipver = 4;
                    $this->ipout = $ipv4addr;
                }

                // Look for an IPv4 address embedded as ::x.x.x.x
            } elseif (0 === strpos($this->ipin, '::')) {
                $ipv4addr = substr($this->ipin, 2);
                if (filter_var($ipv4addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $this->ipver = 4;
                    $this->ipout = $ipv4addr;
                }

                // Otherwise, assume this an IPv6 address.
            } else {
                $this->ipver = 6;
                $this->ipout = $this->ipin;
            }
        }
    }

    /** Check whether the given address is an IP address
     *
     * @param string $ip Given IP address
     *
     * @return string A if IPv4, AAAA if IPv6 or 0 if invalid
     */
    public function isValidIpAddress($ip)
    {
        $value = 0;
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $value = 'A';
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $value = 'AAAA';
        }

        return $value;
    }
}
