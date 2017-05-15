<?php

/* subnet calculator */
class SubnetCalculator
{

    /**
     * @var string
     */
    private $ip;

    /**
     * @var int
     */
    private $network;

    /**
     * @var array
     */
    private $quads = [];

    /**
     * @var int
     */
    private $subnet_mask;

    /**
     * @param string $ip
     * @param int $cidr
     */
    public function __construct($ip, $cidr)
    {
        $this->ip           = $ip;
        $this->network_size = $cidr;
        $this->quads        = explode('.', $ip);
        $this->subnet_mask  = 0xFFFFFFFF << (32 - $this->network_size);
    }

    /* Getters */

    /**
     * @return string
     */
    public function getIPAddress()
    {
        return $this->ip;
    }

    /**
     * @return int
     */
    public function getNetworkSize()
    {
        return $this->network_size;
    }

    /**
     * @return int
     */
    public function getNumberIPAddresses()
    {
        return pow(2, (32 - $this->network_size));
    }

    /**
     * @return int
     */
    public function getNumberAddressableHosts()
    {
        if ($this->network_size == 32) {
            return 1;
        } elseif ($this->network_size == 31) {
            return 2;
        } else {
            return ($this->getNumberIPAddresses() - 2);
        }
    }

    /**
     * @return array
     */
    public function getIPAddressRange()
    {
        return [$this->getNetworkPortion(), $this->getBroadcastAddress()];
    }

    /**
     * @return string
     */
    public function getBroadcastAddress()
    {
        $network_quads         = $this->getNetworkPortionQuads();
        $number_ip_addresses   = $this->getNumberIPAddresses();
        $network_range_quads   = [];
        $network_range_quads[] = sprintf('%d', ( $network_quads[0] & ( $this->subnet_mask >> 24 ) ) + ( ( ( $number_ip_addresses - 1 ) >> 24 ) & 0xFF ));
        $network_range_quads[] = sprintf('%d', ( $network_quads[1] & ( $this->subnet_mask >> 16 ) ) + ( ( ( $number_ip_addresses - 1 ) >> 16 ) & 0xFF ));
        $network_range_quads[] = sprintf('%d', ( $network_quads[2] & ( $this->subnet_mask >>  8 ) ) + ( ( ( $number_ip_addresses - 1 ) >>  8 ) & 0xFF ));
        $network_range_quads[] = sprintf('%d', ( $network_quads[3] & ( $this->subnet_mask >>  0 ) ) + ( ( ( $number_ip_addresses - 1 ) >>  0 ) & 0xFF ));
        return implode('.', $network_range_quads);
    }

    /**
     * @return array
     */
    public function getIPAddressQuads()
    {
        return $this->quads;
    }

    /**
     * @return string
     */
    public function getIPAddressHex()
    {
        return $this->ipAddressCalculation('%02X');
    }

    /**
     * @return string
     */
    public function getIPAddressBinary()
    {
        return $this->ipAddressCalculation('%08b');
    }

    /**
     * @return string
     */
    public function getSubnetMask()
    {
        return $this->subnetCalculation('%d', '.');
    }

    /**
     * @return string
     */
    public function getSubnetMaskBinary()
    {
        return $this->subnetCalculation('%08b');
    }

    /**
     * @return string
     */
    public function getNetworkPortion()
    {
        return $this->networkCalculation('%d', '.');
    }

    /**
     * @return string
     */
    public function getNetworkPortionBinary()
    {
        return $this->networkCalculation('%08b');
    }

    /**
     * @return string
     */
    public function getHostPortion()
    {
        return $this->hostCalculation('%d', '.');
    }

    /**
     * @return string
     */
    public function getHostPortionBinary()
    {
        return $this->hostCalculation('%08b');
    }

    /*
    *@return array
    */
    public function getNetworkPortionQuads()
    {
        return explode('.', $this->networkCalculation('%d', '.'));
    }

    /**
     * @return array
     */
    public function getSubnetArrayReport()
    {
        return [
            'ip_address_cidr_notation' => $this->getIPAddress() . '/' . $this->getNetworkSize(),
            'ip_address' => [
                'binary' => $this->getIPAddressBinary()
            ],
            'subnet_mask' => [
                'quads'  => $this->getSubnetMask(),
                'binary' => $this->getSubnetMaskBinary()
            ],
            'network_portion' => [
                'quads'  => $this->getNetworkPortion(),
                'binary' => $this->getNetworkPortionBinary()
            ],
            'host_portion' => [
                'quads'  => $this->getHostPortion(),
                'binary' => $this->getHostPortionBinary()
            ],
            'network_size'                => $this->getNetworkSize(),
            'number_of_ip_addresses'      => $this->getNumberIPAddresses(),
            'number_of_addressable_hosts' => $this->getNumberAddressableHosts(),
            'ip_address_range'            => $this->getIPAddressRange(),
            'broadcast_address'           => $this->getBroadcastAddress(),
        ];
    }

    /**
     * @param string $format
     * @param string $separator
     * @return string
     */
    private function ipAddressCalculation($format, $separator = '')
    {
        return implode($separator, array_map(
            function ($x) use ($format) {
                return sprintf($format, $x);
            },
            $this->quads
        ));
    }

    /**
     * @param string $format
     * @param string $separator
     * @return string
     */
    private function subnetCalculation($format, $separator = '')
    {
        $mask_quads   = [];
        $mask_quads[] = sprintf($format, ( $this->subnet_mask >> 24 ) & 0xFF);
        $mask_quads[] = sprintf($format, ( $this->subnet_mask >> 16 ) & 0xFF);
        $mask_quads[] = sprintf($format, ( $this->subnet_mask >>  8 ) & 0xFF);
        $mask_quads[] = sprintf($format, ( $this->subnet_mask >>  0 ) & 0xFF);

        return implode($separator, $mask_quads);
    }

    /**
     * @param string $format
     * @param string $separator
     * @return string
     */
    private function networkCalculation($format, $separator = '')
    {
        $network_quads   = [];
        $network_quads[] = sprintf("$format", $this->quads[0] & ( $this->subnet_mask >> 24 ));
        $network_quads[] = sprintf("$format", $this->quads[1] & ( $this->subnet_mask >> 16 ));
        $network_quads[] = sprintf("$format", $this->quads[2] & ( $this->subnet_mask >>  8 ));
        $network_quads[] = sprintf("$format", $this->quads[3] & ( $this->subnet_mask >>  0 ));

        return implode($separator, $network_quads);
    }

    /**
     * @param string $format
     * @param string $separator
     * @return string
     */
    private function hostCalculation($format, $separator = '')
    {
        $network_quads   = [];
        $network_quads[] = sprintf("$format", $this->quads[0] & ~( $this->subnet_mask >> 24 ));
        $network_quads[] = sprintf("$format", $this->quads[1] & ~( $this->subnet_mask >> 16 ));
        $network_quads[] = sprintf("$format", $this->quads[2] & ~( $this->subnet_mask >>  8 ));
        $network_quads[] = sprintf("$format", $this->quads[3] & ~( $this->subnet_mask >>  0 ));

        return implode($separator, $network_quads);
    }
}