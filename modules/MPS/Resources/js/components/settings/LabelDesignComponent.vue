<template>
  <div>
    <Card dis-hover>
      <p slot="title">{{ $t('item_label_design') }}</p>
      <Row :gutter="16">
        <Col :xs="24" :lg="12">
          <Form :model="form" :label-width="80">
            <FormItem :label="$t('width')">
              <InputNumber v-model="form.width" :formatter="value => `${value}px`" :parser="value => value.replace('px', '')" />
            </FormItem>
            <FormItem :label="$t('height')">
              <InputNumber v-model="form.height" :formatter="value => `${value}px`" :parser="value => value.replace('px', '')" />
            </FormItem>
            <FormItem :label="$t('text')">
              <Input v-model="form.text">
                <Button @click="addText()" slot="append">{{ $t('add_x', { x: $t('text') }) }}</Button>
              </Input>
            </FormItem>
            <FormItem>
              <Button @click="addProductDetails()">
                <Icon type="ios-paper" size="18" />
                {{ $t('item_details') }}
              </Button>
              <Button @click="addPrice()">
                <Icon type="ios-cash" size="18" />
                {{ $t('price') }}
              </Button>
              <Button @click="addTextArea()">
                <Icon type="ios-paper-outline" size="18" />
                {{ $t('add_x', { x: $t('textarea') }) }}
              </Button>
              <Button @click="addBarcode()">
                <Icon type="ios-barcode-outline" size="18" />
                {{ $t('add_x', { x: $t('barcode') }) }}
              </Button>
            </FormItem>
            <FormItem>
              <Button type="primary" :disabled="loading" :loading="loading" @click="save()">
                {{ $t('save') }}
              </Button>
              <Button type="primary" @click="json_input = !json_input">
                {{ $t('load_from_json') }}
              </Button>
              <Dropdown trigger="click">
                <Button type="primary">
                  {{ $t('download') }}
                  <Icon type="ios-arrow-down"></Icon>
                </Button>
                <DropdownMenu slot="list">
                  <DropdownItem><a @click="toSVG()" style="display:block;">SVG</a></DropdownItem>
                  <DropdownItem><a @click="toJSON()" style="display:block;">JSON</a></DropdownItem>
                </DropdownMenu>
              </Dropdown>
            </FormItem>
          </Form>

          <Form v-if="json_input" :model="json_form" :label-width="80">
            <FormItem>
              <Input
                type="textarea"
                v-model="json_form.text"
                :autosize="{ minRows: 5, maxRows: 15 }"
                :placeholder="$t('paste_json_content')"
              />
            </FormItem>
            <FormItem>
              <Button type="primary" @click="loadJSON()">
                {{ $t('load') }}
              </Button>
            </FormItem>
          </Form>
        </Col>
        <Col :xs="24" :lg="12" class="text-center">
          <div style="overflow:auto;">
            <canvas id="canvas" :width="form.width" :height="form.height"></canvas>
          </div>
          <div class="text-right">
            <ButtonGroup class="mt16">
              <Button type="primary" @click="bringToFront()">
                {{ $t('bring_front') }}
              </Button>
              <Button type="primary" @click="sendToBack()">
                {{ $t('send_back') }}
              </Button>
              <Button type="error" @click="deleteSelected()">
                {{ $t('remove') }}
              </Button>
              <Button type="error" @click="clearCanvas()">
                {{ $t('remove_all') }}
              </Button>
            </ButtonGroup>
          </div>
        </Col>
      </Row>
    </Card>
    <Card dis-hover shadow class="mt16">
      <div style="overflow:auto;font-size:12px;" v-html="$t('label_instructions_text')"></div>
    </Card>
  </div>
</template>

<script>
import { fabric } from 'fabric';
// import VueFabric from 'vuejs-fabric';

export default {
  // components: { VueFabric },
  data() {
    return {
      form: {
        text: '',
        width: 400,
        height: 200,
      },
      canvas: null,
      loading: false,
      json_input: false,
      json_form: { text: null },
    };
  },
  watch: {
    'form.width': function(v) {
      this.canvas.setWidth(v);
    },
    'form.height': function(v) {
      this.canvas.setHeight(v);
    },
  },
  mounted() {
    this.canvas = new fabric.Canvas('canvas', {
      width: this.form.width,
      height: this.form.height,
      fireRightClick: true,
      fireMiddleClick: true,
      stopContextMenu: true,
      preserveObjectStacking: true,
    });
    fabric.Object.prototype.cornerColor = 'blue';
    fabric.Object.prototype.cornerStyle = 'circle';
    // fabric.Object.prototype.hasRotatingPoint = false;
    fabric.Object.prototype.transparentCorners = false;
    // this.canvas.on('mouse:dblclick', () => {
    //   this.canvas.remove(this.canvas.getActiveObject());
    // });

    this.$http.get('/app/settings/label').then(res => {
      if (res.data.label_width && res.data.label_height) {
        this.form.width = parseInt(res.data.label_width);
        this.form.height = parseInt(res.data.label_height);
      }
      if (res.data.json_string) {
        this.canvas.loadFromJSON(res.data.json_string, () => {
          this.canvas.renderAll();
        });
      }
    });

    // this.canvas.on('mouse:down', event => {
    //   event.e.preventDefault();
    //   if (event.button === 3) {
    //     if (this.canvas.getActiveObject()) {
    //       this.canvas.remove(this.canvas.getActiveObject());
    //       // console.log('object right click', object);
    //       // } else {
    //       // console.log('canvas right click');
    //     }
    //   }
    // });

    // fabric.Image.fromURL('/storage/images/ean13.gif', img => {
    //     this.canvas.add(img);
    // });
  },
  methods: {
    save() {
      this.loading = true;
      this.canvas.toObject(['id']);
      let label = {
        label_width: this.form.width,
        label_height: this.form.height,
        svg_string: this.canvas.toSVG(),
        json_string: JSON.stringify(this.canvas.toObject(['id', 'editable', 'selectable'])), // JSON.stringify(this.canvas.toJSON()),
      };
      this.$http
        .post('app/settings/label', label)
        .then(res => {
          if (res.data.success) {
            this.$Notice.destroy();
            this.$store.commit('setSVGString', label.svg_string);
            this.$Notice.success({ title: this.$t('saved'), desc: this.$t('settings_saved_text') });
          }
        })
        .finally(() => (this.loading = false));
    },
    toSVG() {
      this.download(this.canvas.toSVG(), 'label', 'svg');
    },
    toJSON() {
      this.download(this.canvas.toJSON(), 'label', 'json');
    },
    loadJSON() {
      if (this.isValidJson(this.json_form.text)) {
        let jd = JSON.parse(this.json_form.text);
        if (jd.version && jd.objects && jd.objects.length) {
          this.canvas.loadFromJSON(jd, () => {
            this.canvas.renderAll();
            this.json_input = false;
          });
        } else {
          this.$Notice.error({ title: this.$t('error'), desc: this.$t('invalid_json'), duration: 10 });
        }
      } else {
        this.$Notice.error({ title: this.$t('error'), desc: this.$t('invalid_json'), duration: 4 });
      }
    },
    isValidJson(json) {
      try {
        JSON.parse(json);
        return true;
      } catch (e) {
        return false;
      }
    },
    toObject() {
      console.log(this.canvas.toObject());
    },
    clearCanvas() {
      this.canvas.clear();
    },
    deleteSelected() {
      if (this.canvas.getActiveObject()) {
        this.canvas.remove(this.canvas.getActiveObject());
      } else {
        this.$Notice.error({ title: this.$t('error'), desc: this.$t('object_x_selected'), duration: 10 });
      }
    },
    bringToFront() {
      if (this.canvas.getActiveObject()) {
        this.canvas.getActiveObject().bringToFront();
      } else {
        this.$Notice.error({ title: this.$t('error'), desc: this.$t('object_x_selected'), duration: 10 });
      }
    },
    sendToBack() {
      if (this.canvas.getActiveObject()) {
        this.canvas.getActiveObject().sendToBack()();
      } else {
        this.$Notice.error({ title: this.$t('error'), desc: this.$t('object_x_selected'), duration: 10 });
      }
    },
    addBarcode(barcodeText = 'BARCODE', height = 50, bottomOffset = 60) {
      let rect = new fabric.Rect({
        rx: 4,
        ry: 4,
        top: 0,
        left: 0,
        fill: '#fff',
        stroke: '#ccc',
        height: height,
        originX: 'center',
        originY: 'center',
        selectable: false,
        width: this.form.width - 20,
      });
      let text = new fabric.Text(barcodeText, {
        fontSize: 16,
        fill: '#333',
        originX: 'center',
        originY: 'center',
        selectable: false,
      });
      let group = new fabric.Group([rect, text], {
        left: 9,
        selectable: false,
        id: 'replace-barcode',
        class: 'replace-barcode',
        top: this.form.height - bottomOffset,
      });
      group.setControlVisible('mtr', false);
      this.canvas.add(group);
    },
    // fromElement() {
    //     let opts = {};
    //     this.canvas.fromElement(SVGElement, opts, img => {
    //         // var img1 = img.scale(0.1).set({ left: 100, top: 100 });
    //         img.set({ left: 10, top: 10 });
    //         this.canvas.add(img);
    //         // this.canvas.add(new fabric.Group([ img1, img2, img3], { left: 200, top: 200 }))
    //     });
    // },
    addProductDetails() {
      this.addTextArea('--- Item Name ---\n--- Other info for item. --- ');
    },
    addPrice() {
      this.addText('--- Price: 1,000.00 ---');
    },
    addTextArea(oText) {
      let opt = {
        left: 10,
        fontSize: 16,
        fillColor: '#000000',
        registeObjectEvent: false,
        width: this.form.width - 20,
        editable: oText ? false : true,
        top: this.form.height / 2 - 30,
      };
      const canvasObj = new fabric.Textbox(oText || 'Please Edit Me,\nEnter for new line', { ...opt, fill: opt.fillColor });
      this.canvas.add(canvasObj);
    },
    addText(oText) {
      if (oText || this.form.text) {
        var text = new fabric.Text(oText || this.form.text, { left: 10, top: 100, fontSize: 16 });
        this.canvas.add(text);
        this.form.text = '';
      }
    },
    download(data, name, type = 'json') {
      if (type == 'json') {
        var data = 'data:text/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(data));
      }
      if (type == 'svg') {
        var data = 'data:image/svg+xml;base64,' + window.btoa(data);
      }
      let downloadAnchorNode = document.createElement('a');
      downloadAnchorNode.setAttribute('href', data);
      downloadAnchorNode.setAttribute('download', name + '.' + type);
      document.body.appendChild(downloadAnchorNode);
      downloadAnchorNode.click();
      downloadAnchorNode.remove();
    },
    readTextFile(file, callback) {
      var rawFile = new XMLHttpRequest();
      rawFile.overrideMimeType('application/json');
      rawFile.open('GET', file, true);
      rawFile.onreadystatechange = function() {
        if (rawFile.readyState === 4 && rawFile.status == '200') {
          callback(rawFile.responseText);
        }
      };
      rawFile.send(null);
    },
  },
};
</script>

<style style="scss">
.canvas-container {
  margin-left: auto;
}
#canvas {
  border-radius: 6px;
  border: 1px solid #ccc;
}
</style>
