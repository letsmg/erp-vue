/**
 * Valida CPF
 */
export const isValidCPF = (cpf: string): boolean => {
  // Remove caracteres não numéricos
  const cleanCPF = cpf.replace(/\D/g, '')

  // Verifica se tem 11 dígitos
  if (cleanCPF.length !== 11) return false

  // Verifica se todos os dígitos são iguais
  if (/(\d)\1{10}/.test(cleanCPF)) return false

  // Calcula dígitos verificadores
  for (let t = 9; t < 11; t++) {
    let d = 0
    for (let c = 0; c < t; c++) {
      d += parseInt(cleanCPF[c]) * ((t + 1) - c)
    }
    d = ((10 * d) % 11) % 10
    if (parseInt(cleanCPF[t]) !== d) return false
  }

  return true
}

/**
 * Valida CNPJ
 */
export const isValidCNPJ = (cnpj: string): boolean => {
  // Remove caracteres não numéricos
  const cleanCNPJ = cnpj.replace(/\D/g, '')

  // Verifica se tem 14 dígitos
  if (cleanCNPJ.length !== 14) return false

  // Verifica se todos os dígitos são iguais
  if (/(\d)\1{13}/.test(cleanCNPJ)) return false

  // Primeiro dígito verificador
  let sum = 0
  let weight = 5
  for (let i = 0; i < 12; i++) {
    sum += parseInt(cleanCNPJ[i]) * weight
    weight = weight === 2 ? 9 : weight - 1
  }
  let remainder = sum % 11
  const digit1 = remainder < 2 ? 0 : 11 - remainder

  if (parseInt(cleanCNPJ[12]) !== digit1) return false

  // Segundo dígito verificador
  sum = 0
  weight = 6
  for (let i = 0; i < 13; i++) {
    sum += parseInt(cleanCNPJ[i]) * weight
    weight = weight === 2 ? 9 : weight - 1
  }
  remainder = sum % 11
  const digit2 = remainder < 2 ? 0 : 11 - remainder

  if (parseInt(cleanCNPJ[13]) !== digit2) return false

  return true
}

/**
 * Valida documento (CPF ou CNPJ)
 */
export const isValidDocument = (document: string): { valid: boolean; type?: 'CPF' | 'CNPJ'; message: string } => {
  const cleanDocument = document.replace(/\D/g, '')

  if (cleanDocument.length === 11) {
    return {
      valid: isValidCPF(cleanDocument),
      type: 'CPF',
      message: isValidCPF(cleanDocument) ? '' : 'CPF inválido'
    }
  }

  if (cleanDocument.length === 14) {
    return {
      valid: isValidCNPJ(cleanDocument),
      type: 'CNPJ',
      message: isValidCNPJ(cleanDocument) ? '' : 'CNPJ inválido'
    }
  }

  return {
    valid: false,
    message: 'O documento deve ter 11 dígitos (CPF) ou 14 dígitos (CNPJ)'
  }
}
