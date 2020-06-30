function owo({success, message, data}) {
  if (success) {
    Object.keys(data).forEach(x => document[x] = data[x])
  }
}