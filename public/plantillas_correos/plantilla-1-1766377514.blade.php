<style>
  /* Estilos encapsulados solo dentro de #email-template */
  #email-template,
  #email-template table,
  #email-template td,
  #email-template a {
    -webkit-text-size-adjust: 100%;
    -ms-text-size-adjust: 100%;
  }

  #email-template table,
  #email-template td {
    mso-table-lspace: 0pt;
    mso-table-rspace: 0pt;
  }

  #email-template img {
    -ms-interpolation-mode: bicubic;
    border: 0;
    outline: none;
    text-decoration: none;
    display: block;
  }

  #email-template {
    margin: 0;
    padding: 0;
    width: 100% !important;
    background-color: #f5f7fa;
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    color: #333333;
  }

  #email-template .email-container {
    max-width: 600px;
    margin: 20px auto;
    background-color: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }

  #email-template .email-header {
    background-color: #1e3a8a;
    color: #ffffff;
    padding: 30px 20px;
    text-align: center;
    font-size: 26px;
    font-weight: bold;
    letter-spacing: 0.5px;
  }

  #email-template .email-body {
    padding: 25px 20px;
    font-size: 16px;
    line-height: 1.6;
  }

  #email-template .email-body p {
    margin-bottom: 15px;
  }

  #email-template .email-body .contenido {
    /* Aquí van estilos específicos solo para #contenido si se necesita */
  }

  #email-template .email-button {
    display: inline-block;
    padding: 12px 25px;
    margin: 20px 0;
    background-color: #2563eb;
    color: #ffffff !important;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: background-color 0.3s ease;
  }

  #email-template .email-button:hover {
    background-color: #1d4ed8;
  }

  #email-template .email-footer {
    background-color: #f0f4f8;
    color: #666666;
    padding: 20px;
    text-align: center;
    font-size: 13px;
  }

  #email-template .email-footer a {
    color: #1e3a8a;
    text-decoration: none;
  }

  @media screen and (max-width: 620px) {
    #email-template .email-container {
      width: 95% !important;
    }

    #email-template .email-header {
      font-size: 22px !important;
    }
  }
</style>

<div id="email-template">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td align="center">
        <table class="email-container" cellpadding="0" cellspacing="0">

          <tr>
            <td class="email-header">
             [nombre_sistema]
            </td>
          </tr>

          <tr>
            <td class="email-body">
              <div class="contenido" id="contenido">
              </div>
            </td>
          </tr>

          <tr>
            <td class="email-footer">
              &copy; [anio_actual] [nombre_sistema]. Todos los derechos reservados.<br>
             
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</div>