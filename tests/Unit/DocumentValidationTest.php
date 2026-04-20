<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DocumentValidationTest extends TestCase
{
    /**
     * Valida CPF
     */
    private function isValidCPF(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) !== 11) return false;
        if (preg_match('/(\d)\1{10}/', $cpf)) return false;

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }

        return true;
    }

    /**
     * Valida CNPJ
     */
    private function isValidCNPJ(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpj) !== 14) return false;
        if (preg_match('/(\d)\1{13}/', $cnpj)) return false;

        $sum = 0;
        $weight = 5;
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $weight;
            $weight = $weight === 2 ? 9 : $weight - 1;
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        if ($cnpj[12] != $digit1) return false;

        $sum = 0;
        $weight = 6;
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $weight;
            $weight = $weight === 2 ? 9 : $weight - 1;
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        if ($cnpj[13] != $digit2) return false;

        return true;
    }

    public function test_valid_cpf()
    {
        $this->assertTrue($this->isValidCPF('529.982.247-25'));
        $this->assertTrue($this->isValidCPF('52998224725'));
    }

    public function test_invalid_cpf()
    {
        // Todos os dígitos iguais
        $this->assertFalse($this->isValidCPF('111.111.111-11'));
        $this->assertFalse($this->isValidCPF('000.000.000-00'));

        // Dígitos incorretos
        $this->assertFalse($this->isValidCPF('529.982.247-00'));
        $this->assertFalse($this->isValidCPF('123.456.789-09'));

        // Formato incorreto
        $this->assertFalse($this->isValidCPF('123.456.789'));
        $this->assertFalse($this->isValidCPF('123456789012'));
    }

    public function test_valid_cnpj()
    {
        $this->assertTrue($this->isValidCNPJ('11.444.777/0001-61'));
        $this->assertTrue($this->isValidCNPJ('11444777000161'));
    }

    public function test_invalid_cnpj()
    {
        // Todos os dígitos iguais
        $this->assertFalse($this->isValidCNPJ('00.000.000/0000-00'));
        $this->assertFalse($this->isValidCNPJ('11.111.111/1111-11'));

        // Dígitos incorretos
        $this->assertFalse($this->isValidCNPJ('11.444.777/0001-00'));
        $this->assertFalse($this->isValidCNPJ('12.345.678/0001-95'));

        // Formato incorreto
        $this->assertFalse($this->isValidCNPJ('11.444.777/0001'));
        $this->assertFalse($this->isValidCNPJ('123456789012345'));
    }
}
