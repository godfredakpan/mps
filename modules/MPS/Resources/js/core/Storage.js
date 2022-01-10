var Global = typeof window !== 'undefined' ? window : global;

module.exports = {
  name: 'localStorage',
  read,
  write,
  update,
  remove,
  each,
  clearAll,
};

function localStorage() {
  return Global.localStorage;
}

function read(key) {
  return JSON.parse(localStorage().getItem(key));
}

function write(key, value) {
  return localStorage().setItem(key, JSON.stringify(value));
}

function update(key, value, main, pro) {
  if (!value) return;
  main = main || 'invoice';
  data = read(main) || {};
  if (pro) {
    if (!data[pro]) {
      data[pro] = {};
    }
    data[pro][key] = value;
  } else {
    data[key] = value;
  }
  return write(main, data);
}

function remove(key) {
  return localStorage().removeItem(key);
}

function each(fn) {
  for (var i = localStorage().length - 1; i >= 0; i--) {
    var key = localStorage().key(i);
    fn(read(key), key);
  }
}

function clearAll() {
  return localStorage().clear();
}
