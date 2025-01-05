<template>
  <div class="flex flex-col items-center justify-center p-4">
    <button @click="toggleCameraVisibility" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
      Scan QR Code
    </button>
    <transition name="fade">
      <div v-if="isCameraVisible" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center">
        <div class="bg-white p-4 rounded-lg">
          <select v-model="selectedConstraints">
            <option v-for="option in constraintOptions" :key="option.label" :value="option.constraints">
              {{ option.label }}
            </option>
          </select>
          <qrcode-stream @decode="handleDecode" :constraints="selectedConstraints" :formats="selectedBarcodeFormats" :track="trackFunctionSelected.value" class="w-full"></qrcode-stream>
          <div>
            <span v-for="format in Object.keys(barcodeFormats)" :key="format" class="barcode-format-checkbox">
              <input type="checkbox" v-model="barcodeFormats[format]" :id="format" />
              <label :for="format">{{ format }}</label>
            </span>
          </div>
          <button @click="toggleCameraVisibility" class="mt-2 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">Close</button>
        </div>
      </div>
    </transition>
    <div v-if="decodedString" class="mt-4 p-2 bg-gray-200 rounded">
      Código escaneado: {{ decodedString }}
    </div>
  </div>
</template>

<script>
import { QrcodeStream } from 'vue-qrcode-reader';
import { ref, computed } from 'vue';

export default {
  components: {
    QrcodeStream
  },
  setup(_, { emit }) {
    const isCameraVisible = ref(false);
    const selectedConstraints = ref({ facingMode: 'environment' });
    const constraintOptions = ref([
      { label: 'Rear Camera', constraints: { facingMode: 'environment' } },
      { label: 'Front Camera', constraints: { facingMode: 'user' } }
    ]);
    const barcodeFormats = ref({
      qr_code: true,
      code_128: true,
      ean_13: true,
      code_39: true
    });
    const selectedBarcodeFormats = computed(() => 
      Object.keys(barcodeFormats.value).filter(format => barcodeFormats.value[format])
    );
    const decodedString = ref('');

    const toggleCameraVisibility = () => {
      isCameraVisible.value = !isCameraVisible.value;
    };

    const handleDecode = (decoded) => {
      console.log('QR Code lido:', decoded);
      decodedString.value = decoded;
      emit('decoded', decoded);
      toggleCameraVisibility();
    };

    const paintOutline = (detectedCodes, ctx) => {
      for (const detectedCode of detectedCodes) {
        const [firstPoint, ...otherPoints] = detectedCode.cornerPoints;

        ctx.strokeStyle = 'red';

        ctx.beginPath();
        ctx.moveTo(firstPoint.x, firstPoint.y);
        for (const { x, y } of otherPoints) {
          ctx.lineTo(x, y);
        }
        ctx.lineTo(firstPoint.x, firstPoint.y);
        ctx.closePath();
        ctx.stroke();
      }
    };

    const paintCenterText = (detectedCodes, ctx) => {
      for (const detectedCode of detectedCodes) {
        const { boundingBox, rawValue } = detectedCode;

        const centerX = boundingBox.x + boundingBox.width / 2;
        const centerY = boundingBox.y + boundingBox.height / 2;

        const fontSize = Math.max(12, (50 * boundingBox.width) / ctx.canvas.width);

        ctx.font = `bold ${fontSize}px sans-serif`;
        ctx.textAlign = 'center';

        ctx.lineWidth = 3;
        ctx.strokeStyle = '#35495e';
        ctx.strokeText(detectedCode.rawValue, centerX, centerY);

        ctx.fillStyle = '#5cb984';
        ctx.fillText(rawValue, centerX, centerY);
      }
    };

    const paintOutlineAndCenterText = (detectedCodes, ctx) => {
      paintOutline(detectedCodes, ctx);
      paintCenterText(detectedCodes, ctx);
    };

    const trackFunctionOptions = [
      { text: 'nothing (default)', value: undefined },
      { text: 'outline', value: paintOutline },
      { text: 'centered text', value: paintCenterText },
      { text: 'outline + centered text', value: paintOutlineAndCenterText }
    ];
    const trackFunctionSelected = ref(trackFunctionOptions[3]);

    return {
      isCameraVisible,
      selectedConstraints,
      constraintOptions,
      barcodeFormats,
      selectedBarcodeFormats,
      toggleCameraVisibility,
      handleDecode,
      decodedString,
      trackFunctionOptions,
      trackFunctionSelected
    };
  }
};
</script>

<style scoped>
button {
  transition: background-color 0.3s;
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
.barcode-format-checkbox {
  margin-right: 10px;
  white-space: nowrap;
  display: inline-block;
}
</style>
