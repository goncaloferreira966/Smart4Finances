<template>
  <div>
    <h2>Processar Fatura</h2>
    <input type="file" @change="onFileChange" accept="image/*" />
    <div v-if="processing">Processando imagem...</div>
    <div v-if="total">
      <h3>Total Extraído:</h3>
      <p>{{ total }}</p>
    </div>
  </div>
</template>

<script>
import Tesseract from 'tesseract.js';

export default {
  data() {
    return {
      processing: false,
      total: null,
    };
  },
  methods: {
    onFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        this.processImage(file);
      }
    },
    async processImage(file) {
      this.processing = true;
      try {
        const { data } = await Tesseract.recognize(file, 'por', {
          logger: m => console.log(m)
        });
        this.total = this.extractTotal(data.text);
      } catch (error) {
        console.error('Erro ao processar imagem:', error);
      }
      this.processing = false;
    },
    // Remove espaços extras ao redor de vírgulas ou pontos (ex.: "119 , 65" → "119,65")
    cleanValue(value) {
      return value.replace(/\s*([,\.])\s*/g, '$1').trim();
    },
    // Remove símbolos de moeda (incluindo "EUR")
    stripSymbols(value) {
      return value.replace(/(?:R\$|\$|€|£|EUR)/gi, '').trim();
    },
    // Método de extração do valor usando uma abordagem em três etapas:
    // 1. Filtra linhas candidatas que contenham palavras-chave e não contenham termos indesejados.
    // 2. Se houver candidatos, prioriza os que não contenham "subtotal".
    // 3. Se não houver candidatos, tenta usar uma linha com "subtotal" ou faz um fallback ignorando linhas com IBAN/SWIFT.
    extractTotal(text) {
      // Divide o texto em linhas, removendo linhas em branco
      const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
      
      // Palavras-chave para identificar linhas relevantes
      const candidateKeywords = ['total', 'pagar', 'pagamento'];
      // Termos que queremos excluir (números de IBAN, SWIFT, BIC)
      const excludedKeywords = ['iban', 'swift', 'bic'];
      
      // Filtra as linhas que contenham ao menos uma palavra-chave e não contenham nenhum termo excluído
      const candidates = lines.filter(line => {
        const lower = line.toLowerCase();
        const hasKeyword = candidateKeywords.some(k => lower.includes(k));
        const hasExcluded = excludedKeywords.some(e => lower.includes(e));
        return hasKeyword && !hasExcluded;
      });
      
      let candidateLine;
      if (candidates.length > 0) {
        // Se houver mais de um candidato, prioriza os que não contenham "subtotal"
        const nonSubtotal = candidates.filter(line => !line.toLowerCase().includes('subtotal'));
        candidateLine = nonSubtotal.length > 0 ? nonSubtotal[nonSubtotal.length - 1] : candidates[candidates.length - 1];
        // Extrai o primeiro número encontrado na linha candidata (ignora símbolos)
        const regex = /(?:r\$|\$|€|£|eur)?\s*(\d+(?:[,.]\d+)?)/i;
        const match = candidateLine.match(regex);
        if (match && match[1]) {
          return this.stripSymbols(this.cleanValue(match[1]));
        }
      }
      
      // Se nenhum candidato for encontrado, tenta buscar uma linha com "subtotal"
      const subtotalLine = lines.find(line => line.toLowerCase().includes('subtotal'));
      if (subtotalLine) {
        const regex = /(?:r\$|\$|€|£|eur)?\s*(\d+(?:[,.]\d+)?)/i;
        const match = subtotalLine.match(regex);
        if (match && match[1]) {
          return this.stripSymbols(this.cleanValue(match[1]));
        }
      }
      
      // Fallback: percorre as linhas de baixo para cima, ignorando linhas com termos excluídos
      for (let i = lines.length - 1; i >= 0; i--) {
        const lower = lines[i].toLowerCase();
        if (!excludedKeywords.some(e => lower.includes(e))) {
          const regex = /(?:r\$|\$|€|£|eur)?\s*(\d+(?:[,.]\d+)?)/i;
          const match = lines[i].match(regex);
          if (match && match[1]) {
            return this.stripSymbols(this.cleanValue(match[1]));
          }
        }
      }
      
      return 'Valor não encontrado';
    }
  }
};
</script>
